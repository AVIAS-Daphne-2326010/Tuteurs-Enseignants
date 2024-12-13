<?php
namespace Blog\Views;

/**
 * Vue du Dispatcher
 * @return void
 */
class Dispatcher {

    /**
     * @param \Blog\Models\Dispatcher $dispatcherModel
     * @param string $errorMessage
     */
    public function __construct(private readonly \Blog\Models\Dispatcher $dispatcherModel, private readonly string $errorMessage) {
    }

    public function showView(): void {
        ?>
        <main>
            <div class="col">
                <h3 class="center-align">Répartiteur de tuteurs enseignants</h3>

                <div class="row" id="forms-section">
                    <div class="col card-panel white z-depth-3 s12 m6" style="padding: 20px; margin-right: 10px">
                        <form class="col s12" action="./dispatcher" method="post" id="pushCoef" onsubmit="showLoading();">
                            <?php
                            $saves = $this->dispatcherModel->showCoefficients($_SESSION['identifier']);
                            if ($saves): ?>
                                <label for="save-selector">Choisir une sauvegarde:</label>
                                <div class="input-field">
                                    <select id="save-selector" name="save-selector">
                                        <option>Choisir une sauvegarde</option>
                                        <?php foreach ($saves as $save): ?>
                                            <option value="<?php echo $save['id_backup']; ?>">
                                                Sauvegarde #<?= $save['id_backup']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>


                            <?php
                            $id_backup = $_POST['save-selector'] ?? 'new';

                            if ($id_backup === 'new') {
                                $defaultCriteria = $this->dispatcherModel->getDefaultCoef();
                                $listCriteria = [];

                                foreach ($defaultCriteria as $key => $value) {
                                    $listCriteria[$key] = $value;
                                }
                            } else {
                                $listCriteria = $this->dispatcherModel->loadCoefficients($_SESSION['identifier'], (int)$id_backup);
                            }

                            foreach ($listCriteria as $criteria) {
                                ?>
                                <div class="row">
                                    <div class="col s6">
                                        <p>
                                            <label>
                                                <input type="checkbox" class="filled-in criteria-checkbox"
                                                       name="criteria_enabled[<?php echo $criteria['name_criteria']; ?>]"
                                                       data-coef-input-id="<?php echo $criteria['name_criteria']; ?>"
                                                       checked="checked" />
                                                <span><?= $criteria['name_criteria']; ?></span>
                                            </label>
                                        </p>
                                    </div>
                                    <div class="col s6">
                                        <div class="input-field">
                                            <input type="number" name="coef[<?= $criteria['name_criteria']; ?>]" id="<?= $criteria['name_criteria']; ?>" min="1" max="100" value="<?= $criteria['coef']; ?>">
                                            <label for="<?= $criteria['name_criteria']; ?>">Coefficient</label>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                            <button class="btn waves-effect waves-light button-margin" type="submit" name="action" value="save">Enregistrer
                                <i class="material-icons right">arrow_downward</i>
                            </button>
                            <button class="btn waves-effect waves-light button-margin" type="submit" name="action" value="generate" id="generate-btn">Générer
                                <i class="material-icons right">send</i>
                            </button>
                        </form>
                    </div>

                    <form class="col card-panel white z-depth-3 s12 m5" style="padding: 20px;" action="./dispatcher" method="post" id="associate-form">
                        <div class="row">
                            <div class="input-field col s6">
                                <input id="Id_teacher" name="Id_teacher" type="text" class="validate">
                                <label for="Id_teacher">Id_teacher</label>
                            </div>
                            <div class="input-field col s6">
                                <input id="Internship_identifier" name="Internship_identifier" type="text" class="validate">
                                <label for="Internship_identifier">Internship_identifier</label>
                            </div>
                            <p class="red-text"><?= $this->errorMessage; ?></p>
                            <div class="col s12">
                                <button class="btn waves-effect waves-light button-margin" type="submit" name="action">Associer
                                    <i class="material-icons right">arrow_downward</i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="loading-section" class="center-align" style="display: none;">
                    <p>Chargement en cours, veuillez patienter...</p>
                </div>

                <?php if (isset($_POST['action']) && $_POST['action'] === 'generate'): ?>
                    <div class="row card-panel white z-depth-3 s12 m6">
                        <div class="col s12">
                            <form class="col s12" action="./dispatcher" method="post">
                                <div class="selection">
                                    <table class="highlight centered">
                                        <thead>
                                        <tr>
                                            <th>Enseignant</th>
                                            <th>N° Stage</th>
                                            <th>Score</th>
                                            <th>Associer</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $dictCoef = $_POST['coef'];
                                        $resultDispatchList = $this->dispatcherModel->dispatcher($dictCoef)[0];
                                        foreach ($resultDispatchList as $resultDispatch):
                                            ?>
                                            <tr>
                                                <td><?= $resultDispatch['id_teacher']; ?></td>
                                                <td><?= $resultDispatch['internship_identifier']; ?></td>
                                                <td><?= $resultDispatch['score']; ?></td>
                                                <td>
                                                    <label class="center">
                                                        <input type="checkbox" name="id_prof[]" class="center-align filled-in" value="<?= $resultDispatch['id_teacher']; ?>" />
                                                        <span></span>
                                                        <input type="hidden" name="id_eleve[]" class="center-align filled-in" value="<?= $resultDispatch['internship_identifier']; ?>" />
                                                        <input type="hidden" name="score[]" class="center-align filled-in" value="<?= $resultDispatch['score']; ?>" />
                                                    </label>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td><span>Tout cocher: </span></td>
                                                <td><p>
                                                        <label>
                                                            <input type="checkbox" class="filled-in" name="select-all" />
                                                            <span></span>
                                                        </label>
                                                    </p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col s12 center">
                                    <input type="hidden" name="selecStudentSubmitted" value="1">
                                    <button class="waves-effect waves-light btn" type="submit">Valider</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const selects = document.querySelectorAll('select');
                M.FormSelect.init(selects);

                const saveSelector = document.getElementById('save-selector');
                if (saveSelector) {
                    saveSelector.addEventListener('change', function () {
                        const form = this.closest('form');
                        form.submit();
                    });
                }

                const checkboxes = document.querySelectorAll('.criteria-checkbox');
                const selectAllCheckbox = document.querySelector('input[type="checkbox"][name="select-all"]');

                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', function () {
                        const isChecked = this.checked;
                        document.querySelectorAll('input[type="checkbox"][name="id_prof[]"]').forEach(checkbox => {
                            checkbox.checked = isChecked;
                        });
                    });
                }

                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function () {
                        const coefInput = document.getElementById(this.dataset.coefInputId);
                        if (this.checked) {
                            coefInput.removeAttribute('disabled');
                        } else {
                            coefInput.setAttribute('disabled', 'disabled');
                        }
                    });
                });

                document.querySelectorAll('.coef-input').forEach(input => {
                    input.addEventListener('input', function () {
                        const value = parseInt(this.value);
                        if (value > 100) {
                            this.value = 100;
                        } else if (value < 0) {
                            this.value = 0;
                        }
                    });
                });
            });

            function showLoading() {
                const loadingSection = document.getElementById('loading-section');
                const formsSection = document.getElementById('forms-section');

                if (loadingSection && formsSection) {
                    loadingSection.style.display = 'block';
                    formsSection.style.display = 'none';
                }
            }
        </script>
        <?php
    }
}
