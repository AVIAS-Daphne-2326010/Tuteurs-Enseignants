/**
 * Partie 1
 */
document.addEventListener(
    'DOMContentLoaded', function () {
        const elems = document.querySelectorAll('select');
        const instances = M.FormSelect.init(elems);

        const searchInputTeacher = document.getElementById('searchTeacher');
        const searchInputInternship = document.getElementById('searchInternship');

        const searchResults = document.getElementById('searchResults');

        if (!searchResults) {
            return;
        }
        searchResults.innerHTML = '<p></p>';

        searchInputTeacher.addEventListener(
            'input', function () {
                const searchTerm = searchInputTeacher.value.trim();

                if (searchTerm.length > 0) {
                    fetchResults(searchTerm, 'searchTeacher');
                } else {
                    searchResults.innerHTML = '<p></p>';
                }
            }
        );

        searchInputInternship.addEventListener(
            'input', function () {
                const searchTerm = searchInputInternship.value.trim();

                if (searchTerm.length > 0) {
                    fetchResults(searchTerm, 'searchInternship');
                } else {
                    searchResults.innerHTML = '<p></p>';
                }
            }
        );
    }
);

/**
 * Prendre les requetes avec une requête AJAX
 *
 * @param query La requête
 * @param searchType Le type de recheche
 */
function fetchResults(query, searchType)
{
    fetch(
        window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(
                {
                    action: 'search',
                    searchType: searchType,
                    search: query,
                }
            ),
        }
    ).then(
        response =>
        {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
                return response.json();
        }
    ).then(
        data =>
        {
            displayResults(data, searchType);
        }
    ).catch(
        error =>
        {
            console.error('Erreur fetch resultats:', error);
            searchResults.innerHTML = '<p>Erreur lors de la récupération des résultats</p>';
        }
    );
}


/**
 * Afficher les résultats avec la requête AJAX
 *
 * @param data The data received from the server.
 * @param action The action used to determine how to display the results.
 */
function displayResults(data, action)
{
    if (searchResults) {
        searchResults.innerHTML = '';
    }

    if (!data || data.length === 0) {
        searchResults.innerHTML = '<p>Aucun résultat trouvé</p>';
        return;
    }

    const ul = document.createElement('ul');
    data.forEach(
        item =>
        {
            const li = document.createElement('li');
            li.textContent = action === 'searchTeacher'
            ? `${item.teacher_name} ${item.teacher_firstname} (ID: ${item.id_teacher})`
            : item.company_name
                ? `${item.company_name}: ${item.internship_identifier} - ${item.student_name} ${item.student_firstname}`
                : `${item.student_number} - ${item.student_name} ${item.student_firstname}`;
            li.classList.add('left-align', 'clickable-result');
            li.addEventListener(
                'click', (event) =>
                {
                    event.preventDefault();
                    if (action === 'searchTeacher') {
                        const teacherField = document.getElementById('searchTeacher');
                        if (teacherField) {
                            teacherField.value = `${item.id_teacher}`;
                        }
                    }
                    if (action === 'searchInternship') {
                        const internshipField = document.getElementById('searchInternship');
                        if (internshipField) {
                            internshipField.value = `${item.internship_identifier}`;
                        }
                    }
                }
            );
        ul.appendChild(li);
        }
    );
    searchResults.appendChild(ul);
}


/**
 * Partie 2: Coefficients
 */
document.addEventListener(
    'DOMContentLoaded', function () {
        const selects = document.querySelectorAll('select');
        M.FormSelect.init(selects);

        const saveSelector = document.getElementById('save-selector');
        const deleteBtn = document.getElementById('delete-btn');

        function toggleDeleteButton()
        {
            if (deleteBtn) {
                if (!saveSelector || saveSelector.value === 'new') {
                    deleteBtn.disabled = true;
                }
                else {
                    deleteBtn.disabled = false;
                }
            }
        }

        toggleDeleteButton();

        if (saveSelector) {
            saveSelector.addEventListener('change', toggleDeleteButton);
        }

        if (saveSelector) {
            saveSelector.addEventListener(
                'change', function () {
                    const form = this.closest('form');
                    form.submit();
                }
            );
        }

        const checkboxes = document.querySelectorAll('.criteria-checkbox');

        checkboxes.forEach(
            checkbox =>
            {
                const hiddenInput = document.querySelector(`input[name="is_checked[${checkbox.dataset.coefInputId}]"]`);
                if (checkbox.checked) {
                    hiddenInput.value = '1';
                } else {
                    hiddenInput.value = '0';
                }

                checkbox.addEventListener(
                    'change', function () {
                        if (this.checked) {
                            hiddenInput.value = '1';
                        } else {
                            hiddenInput.value = '0';
                        }
                    }
                );
            }
        );

        document.querySelectorAll('.coef-input').forEach(
            input =>
            {
                input.addEventListener(
                    'change', function () {
                        let value = parseInt(this.value);

                        if (isNaN(value) || value < 1) {
                            this.value = 1;
                        } else if (value > 100) {
                            this.value = 100;
                        }
                    }
                );
            }
        );
        const criteriaCheckboxes = document.querySelectorAll('.criteria-checkbox');
        const errorMessageElement = document.getElementById('checkboxError');
        const generateBtn = document.getElementById('generate-btn');

        let hasInteracted = false;

        function validateCheckboxes()
        {
            const anyChecked = Array.from(criteriaCheckboxes).some(checkbox => checkbox.checked);

            if (!anyChecked && hasInteracted) {
                errorMessageElement.textContent = 'Veuillez sélectionner au moins un critère.';
                generateBtn.disabled = true;
            } else {
                if (errorMessageElement) {
                    errorMessageElement.textContent = '';
                }
                if (generateBtn) {
                    generateBtn.disabled = !anyChecked;
                }
            }
        }

        criteriaCheckboxes.forEach(
            function (checkbox) {
                checkbox.addEventListener(
                    'change', function () {
                        hasInteracted = true;
                        validateCheckboxes();
                    }
                );
            }
        );


        validateCheckboxes();

        const select = document.getElementById('save-selector');

        if (select) {
            select.addEventListener('change', updateButtonState);
            updateButtonState();
        }
    }
);

document.querySelectorAll('.criteria-checkbox').forEach(
    checkbox =>
    {
        checkbox.addEventListener(
            'change', function () {
                const hiddenInput = document.querySelector(`input[name="is_checked[${this.dataset.coefInputId}]"]`);
                hiddenInput.value = this.checked ? '1' : '0';
            }
        );
    }
);

function showLoading()
{
    const loadingSection = document.getElementById('loading-section');
    const formsSection = document.getElementById('forms-section');

    if (loadingSection && formsSection) {
        loadingSection.style.display = 'block';
        formsSection.style.display = 'none';
    }
}

/**
 *  Partie3: Pagination et bouton tout cocher
 */

document.addEventListener(
    'DOMContentLoaded', function () {
        M.Tooltip.init(
            document.querySelectorAll('.star-rating'), {
                exitDelay: 100,
            }
        );

        M.FormSelect.init(document.querySelectorAll('select'));

        if (document.getElementById("dispatch-table") === null) {
            return;
        }

        const rowsPerPageDropdown = document.getElementById('rows-per-page');
        let rowsPerPage = sessionStorage.getItem("rowsCountDispatcher") ? Number(sessionStorage.getItem("rowsCountDispatcher")) : parseInt(rowsPerPageDropdown.value);
        if (rowsPerPage !== 10) {
            rowsPerPageDropdown.options[rowsPerPage === 20 ? 1 : rowsPerPage === 50 ? 2 : rowsPerPage === 100 ? 3 : 4].selected = true;
        }
        sessionStorage.setItem("rowsCountDispatcher", String(rowsPerPage));

        let rows = document.querySelectorAll('.dispatch-row');
        let totalRows = rows.length;
        let totalPages = Math.ceil(totalRows / rowsPerPage);
        let currentPage = 1;

        const prevButton = document.getElementById('prev-page');
        const nextButton = document.getElementById('next-page');
        const firstButton = document.getElementById('first-page');
        const lastButton = document.getElementById('last-page');
        const pageNumbersContainer = document.getElementById('page-numbers');

        sortTable(7);

        for (let i = 0; i < document.getElementById("dispatch-table").rows[0].cells.length; ++i) {
            document.getElementById("dispatch-table").rows[0].getElementsByTagName("TH")[i].addEventListener(
                'click', () =>
                {
                    sortTable(i);
                }
            );
        }

        /**
         * Trie la table prenant pour id "dispatch-table"
         *
         * @param n numéro désignant la colonne par laquelle on trie le tableau
         */
        function sortTable(n)
        {
            let dir, rows, switching, i, x, y, shouldSwitch, column;
            const table = document.getElementById("dispatch-table");
            switching = true;

            if (table.rows[0].getElementsByTagName("TH")[n].innerHTML.substring(table.rows[0].getElementsByTagName("TH")[n].innerHTML.length - 1) === "▲") { dir = "desc";
            } else { dir = "asc";
            }

            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 1); ++i) {
                    shouldSwitch = false;
                    if (rows[i].id === 'select-all-row'
                        || rows[i + 1].id === 'select-all-row'
                    ) {
                        continue;
                    }

                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];

                    if (dir === "asc") {
                        if ((n < 7 && x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase())
                            || (n === 7 && Number(x.getElementsByTagName("DIV")[0].getAttribute('data-tooltip')) < Number(y.getElementsByTagName("DIV")[0].getAttribute('data-tooltip')))
                            || (n === 8 && x.getElementsByTagName("INPUT")[0].checked < y.getElementsByTagName("INPUT")[0].checked)
                        ) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir === "desc") {
                        if ((n < 7 && x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase())
                            || (n === 7 && Number(x.getElementsByTagName("DIV")[0].getAttribute('data-tooltip')) > Number(y.getElementsByTagName("DIV")[0].getAttribute('data-tooltip')))
                            || (n === 8 && x.getElementsByTagName("INPUT")[0].checked > y.getElementsByTagName("INPUT")[0].checked)
                        ) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                }
            }
            for (i = 0; i < rows[0].cells.length; ++i) {
                column = rows[0].getElementsByTagName("TH")[i].innerHTML;
                if (column.substring(column.length-1) === "▲" || column.substring(column.length-1) === "▼") { table.rows[0].getElementsByTagName("TH")[i].innerHTML = column.substring(0, column.length-2);
                }
                if (i === n) {
                    if (dir === "asc") { table.rows[0].getElementsByTagName("TH")[i].innerHTML += " ▲";
                    } else { table.rows[0].getElementsByTagName("TH")[i].innerHTML += " ▼";
                    }
                }
            }

            showPage(currentPage);
        }

        function showPage(page)
        {
            if (page < 1 || page > totalPages) { return;
            }

            rows = document.querySelectorAll('.dispatch-row');

            currentPage = page;
            updatePageNumbers();

            rows.forEach(row => (row.style.display = 'none'));
            addSelectAllRow();

            const start = (currentPage - 1) * rowsPerPage;
            const end = currentPage * rowsPerPage;
            const visibleRows = Array.from(rows).slice(start, end);
            visibleRows.forEach(row => (row.style.display = ''));

            prevButton.disabled = currentPage === 1;
            nextButton.disabled = currentPage === totalPages;
            firstButton.disabled = currentPage === 1;
            lastButton.disabled = currentPage === totalPages;
        }

        function updatePageNumbers()
        {
            pageNumbersContainer.innerHTML = '';

            const maxVisiblePages = 5;
            const halfWindow = Math.floor(maxVisiblePages / 2);
            let startPage = Math.max(currentPage - halfWindow, 1);
            let endPage = Math.min(currentPage + halfWindow, totalPages);

            if (endPage - startPage + 1 < maxVisiblePages) {
                if (startPage === 1) {
                    endPage = Math.min(startPage + maxVisiblePages - 1, totalPages);
                } else if (endPage === totalPages) {
                    startPage = Math.max(endPage - maxVisiblePages + 1, 1);
                }
            }

            if (startPage > 1) {
                createPageButton(1);
                if (startPage > 2) {
                    addEllipsis();
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                createPageButton(i, i === currentPage);
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    addEllipsis();
                }
                createPageButton(totalPages);
            }
        }

        function createPageButton(page, isActive = false)
        {
            const pageNumberButton = document.createElement('button');
            pageNumberButton.textContent = page;
            pageNumberButton.classList.add('waves-effect', 'waves-light', 'btn');
            pageNumberButton.classList.add('page-number');
            pageNumberButton.disabled = isActive;
            pageNumberButton.addEventListener('click', () => showPage(page));
            pageNumbersContainer.appendChild(pageNumberButton);
        }

        function addEllipsis()
        {
            const ellipsis = document.createElement('span');
            ellipsis.textContent = '...';
            ellipsis.classList.add('pagination-ellipsis');
            pageNumbersContainer.appendChild(ellipsis);
        }

        function addSelectAllRow()
        {
            const tbody = document.querySelector('#dispatch-table tbody');
            let selectAllRow = document.querySelector('#select-all-row');

            if (selectAllRow) {
                selectAllRow.remove();
            }

            selectAllRow = document.createElement('tr');
            selectAllRow.id = 'select-all-row';

            selectAllRow.innerHTML = `<td></td>
                                                          <td></td>
                                                          <td></td>
                                                          <td></td>
                                                          <td></td>
                                                          <td></td>
                                                          <td></td>
                                                          <td></td>
                                                          <td>
                                                              <p>
                                                                  <label class="center">
                                                                       <input type="checkbox" id="select-all-checkbox" class="center-align filled-in" />
                                                                       <span data-type="checkbox">Tout cocher</span>
                                                                   </label>
                                                              </p>
                                                           </td>`;
            tbody.appendChild(selectAllRow);

            const selectAllCheckboxElem = document.getElementById('select-all-checkbox');

            selectAllCheckboxElem.addEventListener(
                'change', function () {
                    const visibleRows = Array.from(rows).slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage);
                    visibleRows.forEach(
                        row =>
                        {
                            const checkbox = row.querySelector('input[type="checkbox"]');
                            checkbox.checked = selectAllCheckboxElem.checked;
                        }
                    );
                    const studentTableCheckboxes = document.querySelectorAll('#student-dispatch-table .dispatch-checkbox');
                    studentTableCheckboxes.forEach(
                        studentCheckbox =>
                        {
                            const [studentTeacherId, studentInternshipIdentifier] = studentCheckbox.value.split('$');
                            visibleRows.forEach(
                                dispatchRow =>
                                {
                                    const dispatchCheckbox = dispatchRow.querySelector('input[type="checkbox"]');
                                    if (dispatchCheckbox) {
                                        const [dispatchTeacherId, dispatchInternshipIdentifier] = dispatchCheckbox.value.split('$');
                                        if (studentTeacherId === dispatchTeacherId && studentInternshipIdentifier === dispatchInternshipIdentifier) {
                                            studentCheckbox.checked = selectAllCheckboxElem.checked;
                                        }
                                    }
                                }
                            );
                        }
                    )
                }
            );
        }

        function toggleSelectAllCheckbox()
        {
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            const visibleRows = Array.from(rows).slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage);
            selectAllCheckbox.checked = visibleRows.every(row => row.querySelector('input[type="checkbox"]').checked);
        }

        function findCheckboxInStudentTable(teacherId, internshipIdentifier)
        {
            const studentTable = document.getElementById('student-dispatch-table');
            if (!studentTable) {
                return null;
            }
            const checkboxes = studentTable.querySelectorAll('.dispatch-checkbox');
            for (const checkbox of checkboxes) {
                const [cbTeacherId, cbInternshipIdentifier] = checkbox.value.split('$');
                if (cbTeacherId === teacherId && cbInternshipIdentifier === internshipIdentifier) {
                    return checkbox;
                }
            }
            return null;
        }



        document.querySelectorAll('.dispatch-row input[type="checkbox"]:not(#select-all-checkbox)').forEach(
            checkbox =>
            {
                checkbox.addEventListener(
                    'change', function () {
                        toggleSelectAllCheckbox();

                        const checkboxValueParts = this.value.split('$');
                        const teacherId = checkboxValueParts[0];
                        const internshipIdentifier = checkboxValueParts[1];

                        const studentTableCheckbox = findCheckboxInStudentTable(teacherId, internshipIdentifier);
                        if (studentTableCheckbox) {
                            studentTableCheckbox.checked = this.checked;
                        }
                    }
                );
            }
        );

        rowsPerPageDropdown.addEventListener(
            'change', function () {
                rowsPerPage = parseInt(rowsPerPageDropdown.value);
                sessionStorage.setItem("rowsCountDispatcher", String(rowsPerPage));
                totalPages = Math.ceil(rows.length / rowsPerPage);
                currentPage = 1;
                showPage(currentPage);
            }
        );

        firstButton.addEventListener('click', () => showPage(1));
        lastButton.addEventListener('click', () => showPage(totalPages));
        prevButton.addEventListener('click', () => showPage(currentPage - 1));
        nextButton.addEventListener('click', () => showPage(currentPage + 1));

        window.addEventListener(
            'resize', function () {
                totalRows = rows.length;
                totalPages = Math.ceil(totalRows / rowsPerPage);
                if (currentPage > totalPages) { currentPage = totalPages;
                }
                showPage(currentPage);
            }
        );

        showPage(currentPage);

    }
);

/**
 * Partie 4: Vue Etudiante
 */
let placedMarkers = new Set();
let selectedMarker = null;
let internshipLocCache, teacherLocCache = null;

document.addEventListener(
    'DOMContentLoaded', function () {

        function getDictCoef()
        {
            var jsonString = document.getElementById('dictCoefJson').value;
            try {
                return JSON.parse(jsonString);
            } catch (e) {
                console.error("Invalid JSON string in dictCoefJson:", e);
                return {};
            }
        }

        function getTeachersForInternship(Internship_identifier, idTeacher)
        {
            const mapElement = document.getElementById("map");
            const mapLoadingOverlay = document.getElementById('map-loading-overlay');

            if (mapElement && mapLoadingOverlay) {
                mapLoadingOverlay.style.display = 'flex';
                mapElement.classList.add('loading');
            }
            fetch(
                window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(
                        {
                            action: 'TeachersForinternship',
                            Internship_identifier: Internship_identifier,
                            dicoCoef: JSON.stringify(getDictCoef())
                        }
                    )
                }
            )
                .then(
                    response =>
                    {
                        if (!response.ok) {
                            return response.text().then(
                                errorText =>
                                {
                                    console.error('Fetch error response:', errorText);
                                    throw new Error('Network response was not ok');
                                }
                            );
                        }
                        return response.json();
                    }
                )
                .then(
                    async data =>
                    {
                        await createNewTable(data);
                        await createTeacherMarkers(data, idTeacher);
                        if (mapElement && mapLoadingOverlay) {
                            mapLoadingOverlay.style.display = 'none';
                            mapElement.classList.remove('loading');

                        }
                    }
                )
                .catch(
                    error =>
                    {
                        console.error('Fetch error:', error);
                        if (mapElement && mapLoadingOverlay) {
                            mapLoadingOverlay.style.display = 'none';
                            mapElement.classList.remove('loading');
                        }
                    }
                );
        }

        const tableBody = document.querySelector('#dispatch-table tbody');

        if (tableBody) {
            let lastClickTime = 0;
            const debounceTime = 5000;

            ['click', 'touchstart'].forEach(
                function (eventType) {
                    tableBody.addEventListener(
                        eventType, function (event) {
                            const currentTime = new Date().getTime();

                            if (currentTime - lastClickTime < debounceTime) {
                                return;
                            }
                            lastClickTime = currentTime;

                            const clickedRow = event.target.closest('tr.dispatch-row');
                            if (!clickedRow) {
                                return;
                            }

                            const clickedCell = event.target.closest('td, th');
                            if (!clickedCell) {
                                return;
                            }

                            const allCells = Array.from(clickedRow.children);

                            const clickedColIndex = allCells.indexOf(clickedCell);


                            const isLastColumn = clickedColIndex === allCells.length - 1;

                            if (isLastColumn) {
                                return;
                            }

                            const clickedRowIdentifier = clickedRow.getAttribute('data-internship-identifier');
                            const [internshipName, internshipIdentifier, idTeacher, internshipAddress] = clickedRowIdentifier.split('$');

                            clearMarkers();
                            getTeachersForInternship(internshipIdentifier, idTeacher);
                            updateCompanyAndTeacherMap(internshipAddress, idTeacher, internshipName).then();
                        }
                    );
                }
            );
        }

        async function createTeacherMarkers(data, idTeacher)
        {
            let addressCache = new Map();
            let teacherAddressCache = new Map();

            async function getGeocode(address)
            {
                if (!addressCache.has(address)) {
                    addressCache.set(address, geocodeAddress(address));
                }
                return await addressCache.get(address);
            }

            for (const row of data) {
                if (placedMarkers.has(row.teacher_name)) {
                    continue;
                }

                const internshipLocation = await getGeocode(row.address);

                let teacherAddresses = await getTeacherAddresses(row.id_teacher);

                if (teacherAddresses.length === 0) {
                    continue;
                }

                const teacherLocations = await Promise.all(
                    teacherAddresses.map(
                        async(item) =>
                        {
                            if (!teacherAddressCache.has(item.address)) {
                                teacherAddressCache.set(item.address, getGeocode(item.address));
                            }
                            return await teacherAddressCache.get(item.address);
                        }
                    )
                );

                const distances = await Promise.all(
                    teacherLocations.map(location => calculateDistanceOnly(internshipLocation, location))
                );

                const minIndex = distances.indexOf(Math.min(...distances));
                const closestTeacherAddress = teacherLocations[minIndex];


                if (row.id_teacher === idTeacher) {
                    selectedMarker = placeMarker(closestTeacherAddress, row.teacher_name, false, "#B22222", "white");
                }
                else {
                    placeMarker(closestTeacherAddress, row.teacher_name, false, "blue", "white");
                }

                placedMarkers.add(row.teacher_name);
            }
        }

        async function createNewTable(data)
        {
            const container = document.querySelector('.dispatch-table-wrapper');

            const existingTable = document.getElementById('student-dispatch-table');
            const existingHeader = document.getElementById('student-dispatch-header');

            if (existingTable) {
                existingTable.remove();
            }
            if (existingHeader) {
                existingHeader.remove();
            }

            const header = document.createElement('h3');
            header.id = 'student-dispatch-header';
            header.textContent = `Résultat pour ${data[0].student_firstname} ${data[0].student_name}`;
            header.className = 'center-align flow-text clickable';
            container.appendChild(header);

            const loadingContainer = document.createElement('div');
            loadingContainer.className = 'center-align row';

            loadingContainer.innerHTML = `
            <div class="center-align">
                <p>
                    Chargement en cours, veuillez patienter...
                </p>
                <div class="progress">
                    <div class="indeterminate"></div>
                </div>
            </div>`;
            container.appendChild(loadingContainer);

            loadingContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

            const newTable = document.createElement('table');
            newTable.className = 'highlight centered responsive-table';
            newTable.id = 'student-dispatch-table';

            const thead = document.createElement('thead');
            thead.classList.add("clickable");
            thead.innerHTML = `
            <tr>
            <th>Enseignant</th>
            <th>
                <div class="tooltip-container tooltipped"
                     data-tooltip="Dernier antécédent
                     d'accompagnement"
                     data-position="top">(?)</div>
                HISTORIQUE
            </th>
            <th>Position</th>
            <th>Discipline</th>
            <th>Entreprise</th>
            <th>Score</th>
            <th>Associer</th>
            </tr>`;
            newTable.appendChild(thead);

            const tbody = document.createElement('tbody');

            for (const row of data) {
                const tr = document.createElement('tr');
                row.distance = await getDistance(row.internship_identifier, row.id_teacher);
                row.discipline = await getDisciplines(row.id_teacher);
                let studentHistory = await getStudentHistory(row.student_number);

                if (studentHistory) {
                    row.date_experience = studentHistory;
                } else {
                    row.date_experience = '❌';
                }

                tr.className = 'student-dispatch-row clickable';
                tr.dataset.internshipIdentifier = `${row.internship_identifier}$${row.id_teacher}`;

                tr.dataset.teacherName = `${row.teacher_name}`;

                tr.innerHTML = `
                <td>${row.teacher_firstname} ${row.teacher_name} (${row.id_teacher})</td>
                <td>${row.date_experience || 'dd/mm/yyyy'}</td>
                <td>${row.distance} min</td>
                <td>${row.discipline}</td>
                <td>${row.company_name}</td>
                <td>
                <div class="star-rating" data-tooltip="${row.score}" data-position="top">
                    ${renderStarsJS(row.score)}
                </div>
                </td>
                <td>
                <p>
                    <label class="center">
                        <input type="checkbox" class="dispatch-checkbox center-align filled-in" name="listTupleAssociate[]"
                            value="${row.id_teacher}$${row.internship_identifier}$${row.score}" />
                        <span data-type="checkbox">Cocher</span>
                    </label>
                </p>
                </td>`;

                tbody.appendChild(tr);
            }

            newTable.appendChild(tbody);
            container.appendChild(newTable);

            const checkboxes = newTable.querySelectorAll('.dispatch-checkbox');

            function findCheckboxInDispatcherTable(teacherId, internshipIdentifier)
            {
                const dispatcherTable = document.getElementById('dispatch-table');
                if (!dispatcherTable) {
                    return null;
                }
                const checkboxes = dispatcherTable.querySelectorAll('.dispatch-checkbox');
                for (const checkbox of checkboxes) {
                    const [cbTeacherId, cbInternshipIdentifier] = checkbox.value.split('$');
                    if (cbTeacherId === teacherId && cbInternshipIdentifier === internshipIdentifier) {
                        return checkbox;
                    }
                }
                return null;
            }

            checkboxes.forEach(
                checkbox =>
                {
                    checkbox.addEventListener(
                        'change', function () {
                            if (this.checked) {
                                checkboxes.forEach(
                                    cb =>
                                    {
                                        if (cb !== this) {
                                            cb.checked = false;
                                        }
                                    }
                                );
                            }
                            const studentTableCheckboxes = newTable.querySelectorAll('.dispatch-checkbox');
                            studentTableCheckboxes.forEach(
                                studentCheckbox =>
                                {
                                    const checkboxValueParts = studentCheckbox.value.split('$');
                                    const teacherId = checkboxValueParts[0];
                                    const internshipIdentifier = checkboxValueParts[1];
                                    const dispatcherTableCheckbox = findCheckboxInDispatcherTable(teacherId, internshipIdentifier);
                                    if (dispatcherTableCheckbox) {
                                        dispatcherTableCheckbox.checked = studentCheckbox.checked;
                                    }
                                }
                            );
                        }
                    );
                }
            );

            M.Tooltip.init(
                document.querySelectorAll('.star-rating'), {
                    exitDelay: 100,
                }
            );

            setSortingListeners();

            loadingContainer.remove();

            const rows = newTable.querySelectorAll('tbody tr');
            rows.forEach(
                row =>
                {
                    const cells = row.querySelectorAll('td:not(:last-child)');
                    cells.forEach(
                        cell =>
                        {
                            cell.addEventListener(
                                'click', () =>
                                {
                                    const teacherName = row.dataset.teacherName;
                                    updateSelectedTeacherMarker(teacherName);
                                }
                            );
                        }
                    );
                }
            );
        }

        function updateSelectedTeacherMarker(teacher_name)
        {
            if (selectedMarker) {
                const previousMarkerElement = selectedMarker.getElement();
                if (previousMarkerElement) {
                    const previousLabel = previousMarkerElement.querySelector(".marker-label");
                    const previousPointer = previousMarkerElement.querySelector(".marker-pointer");

                    if (previousLabel) {
                        previousLabel.style.backgroundColor = "blue";
                    }
                    if (previousPointer) {
                        previousPointer.style.borderTop = "6px solid blue";
                    }
                }
            }

            let selectedTeacherLocation = null;

            const markers = document.querySelectorAll(".enhanced-marker");
            markers.forEach(
                marker =>
                {
                    const label = marker.querySelector(".marker-label");
                    if (label && label.textContent === teacher_name) {

                        label.style.backgroundColor = "#B22222";
                        const pointer = marker.querySelector(".marker-pointer");
                        if (pointer) { pointer.style.borderTop = "6px solid red";
                        }

                        selectedMarker = map.getOverlays().getArray().find(overlay => overlay.getElement() === marker);

                        if (selectedMarker) {
                            const position = selectedMarker.getPosition();
                            if (position) {
                                const lonLat = ol.proj.toLonLat(position);
                                selectedTeacherLocation = { lon: lonLat[0], lat: lonLat[1] };
                            }
                        }
                    }
                }
            );

            if (internshipLocCache && selectedTeacherLocation) {
                displayRoute(internshipLocCache, selectedTeacherLocation);
            }
        }


        function renderStarsJS(score)
        {
            const fullStars = Math.floor(score);
            const decimalPart = score - fullStars;

            const halfStars = (decimalPart >= 0.25 && decimalPart < 0.75) ? 1 : 0;

            const adjustedFullStars = (decimalPart >= 0.75) ? fullStars + 1 : fullStars;

            const emptyStars = 5 - adjustedFullStars - halfStars;

            let stars = '';

            for (let i = 0; i < adjustedFullStars; i++) {
                stars += '<span class="filled"></span>';
            }

            if (halfStars) {
                stars += '<span class="half"></span>';
            }
            for (let i = 0; i < emptyStars; i++) {
                stars += '<span class="empty"></span>';
            }

            return stars;
        }
        /**
         * Trie la table prenant pour id "student-dispatch-table"
         *
         * @param n numéro désignant la colonne par laquelle on trie le tableau
         */
        function sortTable(n)
        {
            let dir, rows, switching, i, x, y, shouldSwitch, column;
            const table = document.getElementById("student-dispatch-table");
            switching = true;

            if (table.rows[0].getElementsByTagName("TH")[n].innerHTML.substring(table.rows[0].getElementsByTagName("TH")[n].innerHTML.length - 1) === "▲") { dir = "desc";
            } else { dir = "asc";
            }

            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 1); ++i) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];
                    if (dir === "asc") {
                        if (n === 2 && Number(x.innerHTML.substring(0, x.innerHTML.indexOf(' '))) > Number(y.innerHTML.substring(0, y.innerHTML.indexOf(' ')))
                            || (n === 5 && Number(x.getElementsByTagName("DIV")[0].getAttribute('data-tooltip')) < Number(y.getElementsByTagName("DIV")[0].getAttribute('data-tooltip')))
                            || (n === 6 && x.getElementsByTagName("INPUT")[0].checked < y.getElementsByTagName("INPUT")[0].checked)
                            || (n >= 0 && n < 5 && n !== 2 && x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase())
                        ) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir === "desc") {
                        if (n === 2 && Number(x.innerHTML.substring(0, x.innerHTML.indexOf(' '))) < Number(y.innerHTML.substring(0, y.innerHTML.indexOf(' ')))
                            || (n === 5 && Number(x.getElementsByTagName("DIV")[0].getAttribute('data-tooltip')) > Number(y.getElementsByTagName("DIV")[0].getAttribute('data-tooltip')))
                            || (n === 6 && x.getElementsByTagName("INPUT")[0].checked > y.getElementsByTagName("INPUT")[0].checked)
                            || (n >= 0 && n < 5 && n !== 2 && x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase())
                        ) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                }
            }
            for (i = 0; i < rows[0].cells.length; ++i) {
                column = rows[0].getElementsByTagName("TH")[i].innerHTML;
                if (column.substring(column.length-1) === "▲" || column.substring(column.length-1) === "▼") {
                    table.rows[0].getElementsByTagName("TH")[i].innerHTML = column.substring(0, column.length-2);
                    if (i === 1 && n !== 1) {
                        M.Tooltip.init(
                            document.querySelectorAll('.tooltipped'), {
                                exitDelay: 100,
                            }
                        );
                    }
                }
                if (i === n) {
                    if (dir === "asc") { table.rows[0].getElementsByTagName("TH")[i].innerHTML += " ▲";
                    } else { table.rows[0].getElementsByTagName("TH")[i].innerHTML += " ▼";
                    }
                }
            }
            if (n === 1) {
                M.Tooltip.init(
                    document.querySelectorAll('.tooltipped'), {
                        exitDelay: 100,
                    }
                );
            }
        }

        function setSortingListeners()
        {
            const table = document.getElementById("student-dispatch-table").rows[0];
            for (let i = 0; i < table.cells.length; ++i) {
                table.getElementsByTagName("TH")[i].addEventListener(
                    'click', () =>
                    {
                        sortTable(i);
                    }
                );
            }
            sortTable(5);
            M.Tooltip.init(
                document.querySelectorAll('.tooltipped'), {
                    exitDelay: 100,
                }
            );
        }
    }
);


/**
 * Partie 5: map OSM
 **/

let map, routeLayer, companyMarker;
const teacherMarkerCache = new Map();

/**
 * Initialise la carte, centree sur la France
 *
 * @returns {Promise<void>}
 */

async function initMap()
{
    const mapElement = document.getElementById("map");

    if (!mapElement) { return;
    }

    try {
        const franceCenter = ol.proj.fromLonLat([2.337, 46.227]);

        map = new ol.Map(
            {
                target: mapElement,
                layers: [
                    new ol.layer.Tile(
                        {
                            source: new ol.source.OSM(),
                        }
                    ),
                ],
            view: new ol.View(
                {
                    center: franceCenter,
                    zoom: 5,
                    projection: 'EPSG:3857'
                    }
            ),
            }
        );

        markerSource = new ol.source.Vector();

        map.addLayer(clusterLayer);
    } catch (error) {

    }
}



/**
 * Obtenir les différentes adresses d'un professeur
 *
 * @param Id_teacher Identifiant du professeur
 *
 * @returns {Promise<Array<string>>}
 */
async function getTeacherAddresses(Id_teacher)
{
    try {
        const response = await fetch(
            window.location.href, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams(
                    {
                        action: "getTeacherAddresses",
                        Id_teacher: Id_teacher,
                    }
                ),
            }
        );

        if (!response.ok) {
            const errorText = await response.text();
            console.error("Fetch error response:", errorText);
        }

        return response.json();
    } catch (error) {
        console.error("Fetch error:", error);
        return [];
    }
}

/**
 * Requête au serveur pour avoir la distance minimale entre un prof et un stage
 *
 * @param Internship_identifier
 * @param Id_teacher
 *
 * @returns {Promise<number|*[]>}
 */
async function getDistance(Internship_identifier, Id_teacher)
{
    try {
        const response = await fetch(
            window.location.href, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams(
                    {
                        action: "getDistance",
                        Internship_identifier: Internship_identifier,
                        Id_teacher: Id_teacher
                    }
                ),
            }
        );

        if (!response.ok) {
            const errorText = await response.text();
            console.error("Fetch error response:", errorText);
        }

        const data = await response.json();
        return parseInt(data, 10);
    } catch (error) {
        console.error("Fetch error:", error);
        return [];
    }
}

/**
 * Requête au serveur pour avoir les disciplines d'un professeur
 *
 * @param Id_teacher
 *
 * @returns {Promise<number|*[]>}
 */
async function getDisciplines(Id_teacher)
{
    try {
        const response = await fetch(
            window.location.href, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams(
                    {
                        action: "getDisciplines",
                        Id_teacher: Id_teacher
                    }
                ),
            }
        );

        if (!response.ok) {
            const errorText = await response.text();
            console.error("Fetch error response:", errorText);
        }

        return await response.json();
    } catch (error) {
        console.error("Fetch error:", error);
        return [];
    }
}

async function getStudentHistory(Student_number)
{
    try {
        const response = await fetch(
            window.location.href, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams(
                    {
                        action: "getHistory",
                        Student_number: Student_number
                    }
                ),
            }
        );

        if (!response.ok) {
            const errorText = await response.text();
            console.error("Fetch error response:", errorText);
        }

        return await response.json();
    } catch (error) {
        console.error("Fetch error:", error);
        return [];
    }
}

/**
 * Mise à jour de la carte avec deux nouvelles adresses
 *
 * @param {string} internshipAddress Première adresse
 * @param {string} Id_teacher Id du prof
 * @param internshipName adresse de entreprise
 */
async function updateCompanyAndTeacherMap(internshipAddress, Id_teacher, internshipName)
{
    if (!map) {
        console.error("La carte n'est pas initialisée. Appelez initMap d'abord.");
        return;
    }

    try {
        const teacherAddresses = await getTeacherAddresses(Id_teacher);
        const internshipLocation = await geocodeAddress(internshipAddress);

        let closestTeacherAddress = null;
        let minDistance = Infinity;

        if (Array.isArray(teacherAddresses)) {
            for (const teacher of teacherAddresses) {
                const location = await geocodeAddress(teacher.address);
                const distance = await calculateDistanceOnly(internshipLocation, location);

                if (distance < minDistance) {
                    minDistance = distance;
                    closestTeacherAddress = location;
                }
            }
        } else {
            closestTeacherAddress = await geocodeAddress(teacherAddresses.address);
        }


        internshipName = "Entreprise " + internshipName;
        placeMarker(internshipLocation, internshipName, true, "yellow", "black");
        internshipLocCache = internshipLocation;
        teacherLocCache = closestTeacherAddress;
        await displayRoute(internshipLocation, teacherLocCache);

        centerMap(internshipLocation, closestTeacherAddress);
    } catch (error) {
        console.error("Erreur lors de la mise à jour de la carte :", error);
    }
}

function centerMap(location1, location2)
{
    if (!location1 || !location2) {
        return;
    }
    const view = map.getView();
    view.setCenter(
        ol.proj.fromLonLat(
            [
                (location1.lon + location2.lon) / 2,
                (location1.lat + location2.lat) / 2,
            ]
        )
    );
}

/**
 * Géocode une adresse
 *
 * @param {string} address Adresse à géocoder
 *
 * @returns {Promise<Object>} Localisation géocodée { lat, lon }
 */
async function geocodeAddress(address)
{
    const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(
        address
    )}&format=json&limit=1`;

    try {
        const response = await fetch(url);
        const data = await response.json();

        if (data.length > 0 && data[0].lat && data[0].lon) {
            return { lat: parseFloat(data[0].lat), lon: parseFloat(data[0].lon) };
        }
    } catch (error) {
        console.error("Erreur de géocodage :", error);
    }
}

async function calculateDistanceOnly(origin, destination)
{
    const url = `https://router.project-osrm.org/route/v1/driving/${origin.lon},${origin.lat};${destination.lon},${destination.lat}?overview=false`;

    const response = await fetch(url);
    const data = await response.json();

    if (data.routes && data.routes.length > 0) {
        return data.routes[0].distance;
    } else {
        console.error("Aucun itinéraire trouvé.");
        return Infinity;
    }
}

async function displayRoute(origin, destination)
{
    const url = `https://router.project-osrm.org/route/v1/driving/${origin.lon},${origin.lat};${destination.lon},${destination.lat}?overview=full&geometries=geojson`;

    try {
        const response = await fetch(url);
        const data = await response.json();

        if (data.routes && data.routes.length > 0) {
            const route = data.routes[0];
            const routeCoords = route.geometry.coordinates.map(
                (coord) =>
                ol.proj.fromLonLat(coord)
            );

            if (!routeLayer) {
                routeLayer = new ol.layer.Vector(
                    {
                        source: new ol.source.Vector(),
                        style: new ol.style.Style(
                            {
                                stroke: new ol.style.Stroke(
                                    {
                                        color: "#B22222",
                                        width: 3,
                                    }
                                ),
                            }
                        ),
                    }
                );
                map.addLayer(routeLayer);
            }

            const routeFeature = new ol.Feature(
                {
                    geometry: new ol.geom.LineString(routeCoords),
                }
            );

            routeLayer.setSource(
                new ol.source.Vector(
                    {
                        features: [routeFeature],
                    }
                )
            );

            map.render();
        }
    } catch (error) {
        console.error("Erreur lors de la récupération de l'itinéraire :", error);
    }
}

function placeMarker(location, label, isCompany, bgColor, labelColor)
{
    if (teacherMarkerCache.has(label)) {
        return;
    }

    const markerElement = createMarkerElement(label, bgColor, labelColor);

    const marker = new ol.Overlay(
        {
            position: ol.proj.fromLonLat([location.lon, location.lat]),
            element: markerElement,
        }
    );


    map.addOverlay(marker);

    teacherMarkerCache.set(label, marker);

    if (isCompany) {
        companyMarker = marker;
    } else {
        teacherMarker = marker;
    }

    return marker;
}

/**
 * Crée un élément de marqueur amélioré
 *
 * @param {string} label Étiquette du marqueur
 * @param bgColor
 * @param labelColor
 *
 * @returns {HTMLElement} Élément du marqueur
 */
function createMarkerElement(label, bgColor, labelColor)
{
    const marker = document.createElement("div");
    marker.className = "enhanced-marker";

    const markerLabel = document.createElement("div");
    markerLabel.className = "marker-label";
    markerLabel.textContent = label;

    const pointer = document.createElement("div");
    pointer.className = "marker-pointer";

    marker.appendChild(markerLabel);
    marker.appendChild(pointer);

    marker.style.position = "absolute";
    marker.style.display = "flex";
    marker.style.flexDirection = "column";
    marker.style.alignItems = "center";
    marker.style.zIndex = "1"

    markerLabel.style.backgroundColor = bgColor;
    markerLabel.style.color = labelColor;
    markerLabel.style.padding = "2px 5px";
    markerLabel.style.borderRadius = "3px";
    markerLabel.style.fontSize = "10px";
    markerLabel.style.textAlign = "center";
    markerLabel.style.boxShadow = "0 1px 3px rgba(0, 0, 0, 0.2)";

    pointer.style.width = "0";
    pointer.style.height = "0";
    pointer.style.borderLeft = "4px solid transparent";
    pointer.style.borderRight = "4px solid transparent";
    pointer.style.borderTop = "6px solid " + bgColor;
    pointer.style.marginTop = "-1px";

    return marker;
}

function clearMarkers()
{
    if (!map || typeof map.getOverlays !== "function") {
        return;
    }

    map.getOverlays().clear();

    teacherMarkerCache.clear();
    placedMarkers.clear();
    companyMarker = null;
    internshipLocCache = null;
    teacherLocCache = null;
}

initMap();