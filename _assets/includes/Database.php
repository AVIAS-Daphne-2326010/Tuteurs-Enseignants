<?php
/**
 * Fichier contenant le singleton de l'instance de la base de donnée
 *
 * PHP version 8.3
 *
 * @category Includes
 * @package  Assetsincludes
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
namespace Includes;
use PDO;
use PDOException;

/**
 * Singleton de la base de donnée
 *
 * PHP version 8.3
 *
 * @category Includes
 * @package  Assetsincludes
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class Database
{
    private string $_host = "postgresql-tutormap.alwaysdata.net";
    private string $_user = "tutormap";
    private string $_pass = "8exs7JcEpGVfsI";
    private string $_dbname = "tutormap_v4";
    private ?PDO $_conn = null;

    /**
     * Constructeur de la classe database
     */
    public function __construct()
    {
    }

    /**
     * Méthode statique pour obtenir l'instance unique
     * de la classe database (singleton)
     *
     * @return Database le singleton
     */
    public static function getInstance(): Database
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * Initialise la connexion à la base de données si ce n'est pas déjà fait
     *
     * @return void
     */
    public function initConnection(): void
    {
        if ($this->_conn === null) {
            try {
                $this->_conn = new PDO(
                    "pgsql:host=$this->_host;dbname=$this->_dbname",
                    $this->_user, $this->_pass
                );
                $this->_conn->setAttribute(
                    PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION
                );
            } catch (PDOException $e) {
                echo "Erreur de connexion: " . $e->getMessage();
            }
        }
    }

    /**
     * Méthode pour récupérer la connexion PDO
     * Cette méthode initialise la connexion si elle n'est pas déjà établie
     *
     * @return PDO
     */
    public function getConn(): PDO
    {
        $this->initConnection();
        return $this->_conn;
    }
}