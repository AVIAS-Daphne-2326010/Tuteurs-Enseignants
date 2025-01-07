<?php

namespace Blog\Models;

use Includes\Database;
use PDO;
use PDOException;

class Internship extends Model {
    private Database $db;

    public function __construct(Database $db){
        $this->db = $db;
    }

    /**
     * Récupère les informations relatives aux stages et alternances à venir ou en cours dont l'enseignant passé en paramètre est le tuteur
     * @param string $teacher numéro de l'enseignant
     * @return array tableau (pouvant être vide s'il n'y a aucun résultat ou qu'il y a eu une erreur) contenant le nom de l'entreprise, son adresse, le sujet du stage, son type, le nom et prénom de l'étudiant, sa formation et son groupe
     */
    public function getInterns(string $teacher): array {
        $query = 'SELECT company_name, internship_subject, address, student_name, student_firstname, type, formation, class_group, internship.student_number, internship_identifier, id_teacher
                    FROM internship
                    JOIN student ON internship.student_number = student.student_number
                    WHERE id_teacher = :teacher
                    AND end_date_internship > CURRENT_DATE';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':teacher', $teacher);
        $stmt->execute();
        $studentsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$studentsList) return array();

        foreach($studentsList as &$row) {
            // le nombre de stages complétés par l'étudiant
            $internships = $this->globalModel->getInternships($row['student_number']);

            // l'année durant laquelle le dernier stage/alternance de l'étudiant a eu lieu avec l'enseignant comme tuteur
            $row['year'] = "";

            // le nombre de fois où l'enseignant a été le tuteur de l'étudiant
            $row['internshipTeacher'] = $internships ? $this->globalModel->getInternshipTeacher($internships, $teacher, $row['year']) : 0;

            // durée en minute séparant l'enseignant de l'adresse de l'entreprise où l'étudiant effectue son stage
            $row['duration'] = $this->globalModel->getDistance($row['internship_identifier'], $teacher, isset($row['id_teacher']));
        }

        return $studentsList;
    }

    public function getCountInternsPerType(array $interns, &$internship, &$alternance): void {
        $internship = 0;
        $alternance = 0;
        if (empty($interns)) return;

        foreach ($interns as $intern) {
            if (strtolower($intern['type']) == 'internship') ++$internship;
            if (strtolower($intern['type']) == 'alternance') ++$alternance;
        }
    }

    /**
     * Calcule la pertinence des stages pour un professeur et des stages en fonction de plusieurs critères de pondération.
     *
     * @param String $identifier Identifiant du professeur
     * @param array $dictCoef Tableau associatif des critères de calcul et leurs coefficients
     * @return array|array[] Tableau d'associations ('id_teacher', 'internship_identifier', 'score' et type')
     */
    /**
     * @param array $teacher
     * @param array $dictCoef
     * @return array|array[]
     */
    public function RelevanceTeacher(array $teacher, array $dictCoef): array
    {
        $identifier = $teacher['id_teacher'];

        $internshipList = array();
        $departments = $this->globalModel->getDepTeacher($identifier);
        foreach($departments as $listDepTeacher) {
            foreach($listDepTeacher as $department) {
                $newList = $this->globalModel->getInternshipsPerDepartment($department);
                if ($newList)  {
                    $internshipList = array_merge($internshipList, $newList);
                }
            }
        }

        $result = array();

        foreach($internshipList as $internship) {
            $result[] = $this->calculateRelevanceTeacherStudentsAssociate($teacher, $dictCoef, $internship);
        }

        if (!empty($result)) {
            return $result;
        }
        return [[]];
    }

    public function RelevanceInternship(string $internship, array $dictCoef): array
    {
        $db = $this->db;

        $query = "SELECT Teacher.Id_teacher, Teacher.teacher_name, Teacher.teacher_firstname, Teacher.maxi_number_trainees, 
                    SUM(CASE 
                    WHEN internship.type = 'alternance' THEN 2 
                    WHEN internship.type = 'Internship' THEN 1 
                    ELSE 0
                    END) AS Current_count FROM Teacher 
                  JOIN has_role ON Teacher.Id_teacher = has_role.user_id
                  JOIN Study_at ON Study_at.department_name = has_role.department_name
                  JOIN Student ON Student.student_number = Study_at.student_number
                  JOIN INTERNSHIP ON Internship.student_number = Student.student_number
                  WHERE has_role.department_name IN (SELECT department_name 
                                            FROM Study_at 
                                            JOIN Student ON Study_at.student_number = Student.student_number
                                            JOIN Internship ON Internship.student_number = Internship.student_number
                                            WHERE Internship.internship_identifier = :internship
                                            GROUP BY department_name) AND Internship.internship_identifier = :internship
                  GROUP BY Teacher.Id_teacher, Teacher.teacher_name, Teacher.teacher_firstname, Teacher.maxi_number_trainees
                    HAVING Teacher.maxi_number_trainees > SUM(
                    CASE 
                        WHEN internship.type = 'alternance' THEN 2
                        WHEN internship.type = 'Internship' THEN 1
                        ELSE 0
                    END) 
                  ";

        $stmt = $db->getConn()->prepare($query);
        $stmt->bindValue(':internship', $internship);
        $stmt->execute();
        $teacherList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $query = "SELECT Internship.Internship_identifier, Internship.Company_name, Internship.Internship_subject, Internship.Address, Internship.Student_number, Internship.Type, Student.Student_name, Student.Student_firstname, Student.Formation, Student.Class_group
                    FROM Internship JOIN Student ON Internship.Student_number = Student.Student_number WHERE Internship.internship_identifier = :internship";

        $stmt = $db->getConn()->prepare($query);
        $stmt->bindValue(':internship', $internship);
        $stmt->execute();
        $internship = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $result = array();

        foreach($teacherList as $teacher) {
            $result[] = $this->calculateRelevanceTeacherStudentsAssociate($teacher, $dictCoef, $internship[0]);
        }

        if (!empty($result)) {
            usort($result, fn($a, $b) => $b['score'] <=> $a['score']);
            return $result;
        }
        return [[]];
    }

    public function calculateRelevanceTeacherStudentsAssociate(array $teacher, array $dictCoef, array $internship): array{
        $identifier = $teacher['id_teacher'];
        $dictValues = array();

        // Calculer les valeurs uniquement si elles sont nécessaires
        if (isset($dictCoef['Distance'])) {
            $dictValues["Distance"] = $this->globalModel->getDistance($internship['internship_identifier'], $identifier, isset($internship['id_teacher']));
        }

        if (isset($dictCoef['Cohérence'])) {
            $dictValues["Cohérence"] = round($this->globalModel->scoreDiscipSubject($internship['internship_identifier'], $identifier), 2);
        }

        if (isset($dictCoef['A été responsable'])) {
            $internshipListData = $this->globalModel->getInternships($internship['internship_identifier']);
            $dictValues["A été responsable"] = $internshipListData;
        }

        if (isset($dictCoef['Est demandé'])) {
            $dictValues["Est demandé"] = $this->globalModel->isRequested($internship['internship_identifier'], $identifier);
        }

        $totalScore = 0;
        $totalCoef = 0;

        // Pour chaque critère dans le dictionnaire de coefficients, calculer le score associé
        foreach ($dictCoef as $criteria => $coef) {
            if (isset($dictValues[$criteria])) {
                $value = $dictValues[$criteria];

                switch ($criteria) {
                    case 'Distance':
                        $ScoreDuration = $coef / (1 + 0.02 * $value);
                        $totalScore += $ScoreDuration;
                        break;

                    case 'A été responsable':
                        $numberOfInternships = count($value);
                        $baselineScore = 0.7 * $coef;

                        if ($numberOfInternships > 0) {
                            $ScoreInternship = $coef * min(1, log(1 + $numberOfInternships, 2));
                        } else {
                            $ScoreInternship = $baselineScore;
                        }

                        $totalScore += $ScoreInternship;
                        break;

                    case 'Est demandé':
                    case 'Cohérence':
                        $ScoreRelevance = $value * $coef;
                        $totalScore += $ScoreRelevance;
                        break;

                    default:
                        $totalScore += $value * $coef;
                        break;
                }
                $totalCoef += $coef;
            }
        }

        // Score normalise sur 5
        $ScoreFinal = ($totalScore * 5) / $totalCoef;

        $newList = ["id_teacher" => $identifier, "teacher_name" => $teacher["teacher_name"], "teacher_firstname" => $teacher["teacher_firstname"], "student_number" => $internship["student_number"], "student_name" => $internship["student_name"], "student_firstname" => $internship["student_firstname"], "internship_identifier" => $internship['internship_identifier'], "internship_subject" => $internship["internship_subject"], "address" => $internship["address"], "company_name" => $internship["company_name"], "formation" => $internship["formation"], "class_group" => $internship["class_group"], "score" => round($ScoreFinal, 2), "type" => $internship['type']];

        if (!empty($newList)) {
            return $newList;
        }

        return [[]];
    }

    /**
     * Permet de trouver la meilleure combinaison possible tuteur-stage et le renvoie sous forme de tableau
     * @param array $dicoCoef dictionnaire cle->nom_critere et valeur->coef
     * @return array|array[] resultat final sous forme de matrice
     */
    public function dispatcher(array $dicoCoef): array
    {
        $db = $this->db;

        $roleDepartments = $_SESSION['role_department'];
        $placeholders = implode(',', array_fill(0, count($roleDepartments), '?'));

        $query = "SELECT Teacher.Id_teacher, Teacher.teacher_name, Teacher.teacher_firstname,
              MAX(maxi_number_trainees) AS Max_trainees, 
              SUM(CASE 
              WHEN internship.type = 'alternance' THEN 2 
              WHEN internship.type = 'Internship' THEN 1 
              ELSE 0
              END) AS Current_count
              FROM Teacher
              JOIN has_role ON Teacher.Id_teacher = Has_role.user_id
              LEFT JOIN internship ON Teacher.Id_teacher = internship.Id_teacher
              WHERE department_name IN ($placeholders)
              GROUP BY Teacher.Id_teacher";

        $stmt = $db->getConn()->prepare($query);
        $stmt->execute($roleDepartments);

        $teacherData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $listTeacherMax = [];
        $listTeacherIntership = [];
        foreach ($teacherData as $teacher) {
            $listTeacherMax[$teacher['id_teacher']] = $teacher['max_trainees'];
            $listTeacherIntership[$teacher['id_teacher']] = $teacher['current_count'];
        }

        $listFinal = [];
        $listStart = [];
        $listEleveFinal = [];

        foreach ($teacherData as $teacher) {
            foreach ($this->RelevanceTeacher($teacher, $dicoCoef) as $association) {
                $listStart[] = $association;
            }
        }

        if (empty($listStart)) {
            return [[], []];
        }

        $assignedCounts = $listTeacherIntership;

        while (!empty($listStart)) {
            usort($listStart, fn($a, $b) => $b['score'] <=> $a['score']);
            $topCandidate = $listStart[0];
            if ($assignedCounts[$topCandidate['id_teacher']] < $listTeacherMax[$topCandidate['id_teacher']] &&
                !in_array($topCandidate['internship_identifier'], $listEleveFinal) && $topCandidate['type'] === 'Internship') {
                if ($topCandidate['type'] === 'Internship' && $listTeacherMax[$topCandidate['id_teacher']] - $assignedCounts[$topCandidate['id_teacher']] > 1)  {
                    $listFinal[] = $topCandidate;
                    $listEleveFinal[] = $topCandidate['internship_identifier'];
                    $assignedCounts[$topCandidate['id_teacher']] += 1;
                }
                elseif ($topCandidate['type'] === 'alternance' && $listTeacherMax[$topCandidate['id_teacher']] - $assignedCounts[$topCandidate['id_teacher']] > 2){
                    $listFinal[] = $topCandidate;
                    $listEleveFinal[] = $topCandidate['internship_identifier'];
                    $assignedCounts[$topCandidate['id_teacher']] += 2;
                }
                else array_shift($listStart);
            }
            else
                array_shift($listStart);
        }
        return [$listFinal, $assignedCounts];
    }

    /**
     * Récupère une liste des identifiants de stages des étudiants inscrits dans les départements associés au rôle de l'admin.
     *
     * @return array|false Un tableau contenant les identifiants des stages ou `false` en cas d'erreur ou si aucun stage n'est trouvé pour les départements spécifiés.
     */

    public function createListInternship() {
        $roleDepartments = $_SESSION['role_department'];
        $placeholders = implode(',', array_fill(0, count($roleDepartments), '?'));

        $query = "SELECT Internship.Internship_identifier FROM Internship JOIN Study_at ON Internship.Student_number = Study_at.Student_number
                    where Department_name IN ($placeholders)";
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->execute($roleDepartments);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Récupère une liste des élèves et professeurs associés inscrits dans les départements dont l'admin est responsable.
     *
     * @return array|false Un tableau contenant les paires (id_teacher, internship_identifier) pour chaque étudiant dans les départements concernés, ou `false` en cas d'erreur ou si aucun résultat n'est trouvé.
     */
    public function createListAssociate() {
        $roleDepartments = $_SESSION['role_department'];
        $placeholders = implode(',', array_fill(0, count($roleDepartments), '?'));

        $query = "SELECT internship.Id_teacher, internship.Internship_identifier FROM internship JOIN Study_at ON internship.Student_number = Study_at.Student_number
                    WHERE Department_name IN ($placeholders) AND internship.Id_teacher IS NOT NULL";
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->execute($roleDepartments);
        return $stmt->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * Fonction permettant d'associer un enseignant à un stage.
     *
     * @return string Un message confirmant l'enregistrement de l'association entre l'enseignant et le stage.
     */
    public function insertResponsible() {
        $query = 'UPDATE internship SET Id_teacher = :Id_teacher WHERE Internship_identifier = :Internship_identifier';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':Internship_identifier', $_POST['searchInternship']);
        $stmt->bindParam(':Id_teacher', $_POST['searchTeacher']);
        $stmt->execute();
        return "Association " . $_POST['searchTeacher'] . " et " . $_POST['searchInternship'] . " enregistrée.";
    }

    /**
     * Cette fonction effectue la mise à jour des informations dans la base de données en associant un enseignant et un score de pertinence à un stage.
     *
     * **Paramètres :**
     * - `String $id_prof` : L'identifiant de l'enseignant à associer au stage.
     * - `String $Internship_id` : L'identifiant du stage auquel l'enseignant est affecté.
     * - `float $Score` : Le score de pertinence attribué à cette association (représente la qualité de la répartition).
     *
     * @return string Message confirmant l'enregistrement de l'association entre l'enseignant et le stage.."
     */
    public function insertIs_responsible(String $id_prof, String $Internship_id, float $Score) {
        $query = 'UPDATE internship SET Id_teacher = :id_prof, Relevance_score = :Score WHERE Internship_identifier = :Internship_id';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':id_prof', $id_prof);
        $stmt->bindParam(':Score', $Score);
        $stmt->bindParam(':Internship_id', $Internship_id);
        $stmt->execute();
        return "Association " . $id_prof . " et " . $Internship_id . " enregistrée. <br>";
    }
}