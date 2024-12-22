<?php
namespace Blog\Views;

/**
 * Vue du Dispatcher
 * @return void
 */
class Dispatcher {

    /**
     * @param \Blog\Models\Dispatcher $dispatcherModel
     * @param string $errorMessageAfterSort
     * @param string $errorMessageDirectAssoc
     * @param string $checkMessageDirectAssoc
     * @param string $checkMessageAfterSort
     */
    public function __construct(private readonly \Blog\Models\Dispatcher $dispatcherModel, private readonly string $errorMessageAfterSort, private readonly string $errorMessageDirectAssoc, private readonly string $checkMessageDirectAssoc, private readonly string $checkMessageAfterSort) {
    }

    public function showView(): void {
        ?>
        <main>
            <div class="col">

                <h3 class="center-align flow-text">Répartiteur de tuteurs enseignants</h3>

                <div class="row" id="forms-section">
                    <form class="col s10 m6 card-panel white z-depth-3" style="padding: 20px; margin-right: 10px" id="coefficients-form" method="post">
                        <?php
                        $saves = $this->dispatcherModel->showCoefficients();
                        if ($saves): ?>
                        <div class="input-field">
                            <label for="save-selector"></label>
                            <select id="save-selector" name="save-selector">
                                <option value='default'>Choisir une sauvegarde</option>
                                <?php foreach ($saves as $save): ?>
                                    <option value="<?php echo $save['id_backup']; ?>">
                                        Sauvegarde #<?= $save['id_backup']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <?php
                        $id_backup = $_POST['save-selector'] ?? 'default';

                        if ($id_backup === 'default' || $id_backup === 'new') {
                        $defaultCriteria = $this->dispatcherModel->getDefaultCoef();
                        $listCriteria = [];

                        foreach ($defaultCriteria as $key => $value) {
                        $listCriteria[$key] = $value;
                        }
                        } else {
                        $listCriteria = $this->dispatcherModel->loadCoefficients($_SESSION['identifier'], $id_backup);
                        }

                        $dictCoef = [];

                        foreach ($listCriteria as $criterion) {
                            if ($criterion['is_checked']) {
                                $dictCoef[$criterion['name_criteria']] = $criterion['coef'];
                            }
                        }

                        $escapedJsonCriterias = htmlspecialchars(json_encode($dictCoef), ENT_QUOTES);
                        echo "<input type='hidden' id='dictCoefJson' value='" . $escapedJsonCriterias . "'>";
                        ?>

                        <?php foreach ($listCriteria as $criteria): ?>
                        <div class="row">
                            <div class="col s6">
                                <p>
                                    <label>
                                        <input type="hidden" name="is_checked[<?php echo $criteria['name_criteria']; ?>]" value="0">
                                        <input type="checkbox" class="filled-in criteria-checkbox"
                                               name="criteria_enabled[<?php echo $criteria['name_criteria']; ?>]"
                                               data-coef-input-id="<?php echo $criteria['name_criteria']; ?>"
                                               <?php if ($criteria['is_checked']): ?>checked="checked"<?php endif; ?> />
                                        <span><?= $criteria['name_criteria']; ?></span>
                                    </label>
                                </p>
                            </div>
                            <div class="col s6">
                                <div class="input-field">
                                    <input type="number" name="coef[<?= $criteria['name_criteria']; ?>]" id="<?= $criteria['name_criteria']; ?>"
                                           min="1" max="100" value="<?= $criteria['coef']; ?>" />
                                    <label for="<?= $criteria['name_criteria']; ?>">Coefficient</label>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>


                        <p class="red-text" id="checkboxError"><?php echo $this->errorMessageAfterSort; ?></p>
                        <p class="green-text" id="checkMessageAfterSort"><?php echo $this->checkMessageAfterSort; ?></p>

                        <button class="btn waves-effect waves-light button-margin" type="button" id="save-btn">Enregister
                            <i class="material-icons right">arrow_downward</i>
                        </button>
                        <button class="btn waves-effect waves-light button-margin" type="button" id="generate-btn">Générer
                            <i class="material-icons right">send</i>
                        </button>
                    </form>

                    <form class="col card-panel white z-depth-3 s10 m5" style="padding: 20px;" id="associate-form">
                        <div class="row">
                            <p class="text">Associe un professeur à un stage (ne prend pas en compte le nombre maximum d'étudiant, ni le fait que le stage soit déjà attribué)</p>
                            <div class="input-field col s6">
                                <input id="searchTeacher" name="searchTeacher" type="text" class="validate">
                                <label for="searchTeacher">ID professeur</label>
                            </div>
                            <div class="input-field col s6">
                                <input id="searchInternship" name="searchInternship" type="text" class="validate">
                                <label for="searchInternship">ID Stage</label>
                            </div>
                            <p class="red-text" id="errorMessageDirectAssoc"><?php echo $this->errorMessageDirectAssoc; ?></p>
                            <p class="green-text" id="checkMessageDirectAssoc"><?php echo $this->checkMessageDirectAssoc; ?></p>
                            <div class="col s12">
                                <button class="btn waves-effect waves-light button-margin" type="submit" name="action">Associer
                                    <i class="material-icons right">arrow_downward</i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="loading-section" class="center-align" style="display: none;">
                    <p style="font-size: 24px;">Chargement en cours, veuillez patienter...</p>
                    <div class="progress">
                        <div class="indeterminate"></div>
                    </div>
                </div>

                <div id="results-section">
                    <div class="dispatch-table-wrapper selection table-container">

                    </div>
                </div>


                <div class="dispatcher-controls" id="dispatch-controls" style="display: none;">
                    <div class="row">
                        <div class="input-field col s2">
                            <label for="rows-per-page"></label>
                            <select id="rows-per-page">
                                <option value="10" selected>10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="1000">Tout</option>
                            </select>
                            <label>Nombre de lignes par page</label>
                        </div>
                    </div>

                    <div id="pagination-controls" class="center-align">
                        <button type="button" class="waves-effect waves-light btn" id="first-page"><i class="material-icons">first_page</i></button>
                        <button type="button" class="waves-effect waves-light btn" id="prev-page"><i class="material-icons">arrow_back</i></button>
                        <div id="page-numbers"></div>
                        <button type="button" class="waves-effect waves-light btn" id="next-page"><i class="material-icons">arrow_forward</i></button>
                        <button type="button" class="waves-effect waves-light btn" id="last-page"><i class="material-icons">last_page</i></button>
                    </div>

                    <form action="./dispatcher" method="post">
                        <div class="row s12 center">
                            <input type="hidden" id="selectStudentSubmitted" name="selectStudentSubmitted" value="1">
                            <button class="waves-effect waves-light btn" type="submit">Valider</button>
                            <input type="hidden" name="restartDispatcherButton" value="1">
                            <button class="waves-effect waves-light btn" type="submit">Recommencer</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        <?php
    }
}
