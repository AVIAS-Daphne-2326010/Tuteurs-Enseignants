/**
 * Tri du tableau et pagination
 */

document.addEventListener(
    'DOMContentLoaded', function () {
        if (document.getElementById("tutoring-table") === null) {
            return;
        }

        M.FormSelect.init(
            document.querySelectorAll(
                'select'
            ),
            {
                exitDelay: 100,
            }
        );

        const rowsPerPageDropdown = document.getElementById('rows-per-page');
        let rowsPerPage = sessionStorage.getItem("rowsCountTutor") ? Number(sessionStorage.getItem("rowsCountTutor")) : parseInt(rowsPerPageDropdown.value); // Set default to 10
        if (rowsPerPage !== 10) {
            rowsPerPageDropdown.options[rowsPerPage === 20 ? 1 : rowsPerPage === 50 ? 2 : rowsPerPage === 100 ? 3 : 4].selected = true;
        }
        sessionStorage.setItem("rowsCountTutor", String(rowsPerPage));

        let rows = document.querySelectorAll('.tutoring-row');
        let totalRows = rows.length;
        let totalPages = Math.ceil(totalRows / rowsPerPage);
        let currentPage = sessionStorage.getItem("pageTutor") && Number(sessionStorage.getItem("pageTutor")) <= totalPages
        && rowsPerPage === 10 ? Number(sessionStorage.getItem("pageTutor")) : 1;

        const prevButton = document.getElementById('prev-page');
        const nextButton = document.getElementById('next-page');
        const firstButton = document.getElementById('first-page');
        const lastButton = document.getElementById('last-page');
        const pageNumbersContainer = document.getElementById('page-numbers');

        if (document.getElementById("tutoring-table").rows.length > 2) {
            if (!(sessionStorage.getItem('columnNumberTutor') && sessionStorage.getItem('directionTutor'))) {
                sessionStorage.setItem('columnNumberTutor', "0");
                sessionStorage.setItem('directionTutor', "asc");
            }
            sortTable(Number(sessionStorage.getItem('columnNumberTutor')), true);

            for (let i = 0; i < document.getElementById("tutoring-table").rows[0].cells.length; ++i) {
                document.getElementById("tutoring-table").rows[0].getElementsByTagName("TH")[i].addEventListener(
                    'click', () =>
                    {
                        sortTable(i);
                    }
                );
            }
        }

        /**
         * Trie la table prenant pour id "tutoring-table"
         *
         * @param n numéro désignant la colonne par laquelle on trie le tableau
         * @param firstLoad booléen indiquant si cet appel est le premier depuis le chargement de la page
         */
        function sortTable(n, firstLoad = false)
        {
            let dir, rows, switching, i, x, y, shouldSwitch, column;
            const table = document.getElementById("tutoring-table");
            switching = true;

            if (!firstLoad) {
                if (table.rows[0].getElementsByTagName("TH")[n].innerHTML.substring(table.rows[0].getElementsByTagName("TH")[n].innerHTML.length - 1) === "▲") {
                    dir = "desc";
                } else {
                    dir = "asc";
                }
            } else {
                dir = sessionStorage.getItem('directionTutor');
            }

            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 1); ++i) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];
                    if (dir === "asc") {
                        if ((n !== 6 && n !== 7
                            && x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase())
                            || ((n === 6 || n === 7)
                            && x.getAttribute("data-value").toLowerCase() > y.getAttribute("data-value").toLowerCase())
                        ) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir === "desc") {
                        if ((n !== 6 && n !== 7
                            && x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase())
                            || ((n === 6 || n === 7)
                            && x.getAttribute("data-value").toLowerCase() < y.getAttribute("data-value").toLowerCase())
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
                }
                if (i === n) {
                    if (dir === "asc") { table.rows[0].getElementsByTagName("TH")[i].innerHTML += " ▲";
                    } else {
                        table.rows[0].getElementsByTagName("TH")[i].innerHTML += " ▼";
                    }
                }
            }

            sessionStorage.setItem('columnNumberTutor', n);
            sessionStorage.setItem('directionTutor', dir);
            showPage(currentPage);
        }

        function showPage(page)
        {
            if (page < 1 || page > totalPages) {
                return;
            }

            rows = document.querySelectorAll('.tutoring-row');

            currentPage = page;
            updatePageNumbers();

            rows.forEach(row => row.style.display = 'none');

            const start = (currentPage - 1) * rowsPerPage;
            const end = currentPage * rowsPerPage;
            const visibleRows = Array.from(rows).slice(start, end);
            visibleRows.forEach(row => row.style.display = '');

            prevButton.disabled = currentPage === 1;
            nextButton.disabled = currentPage === totalPages;
            firstButton.disabled = currentPage === 1;
            lastButton.disabled = currentPage === totalPages;

            sessionStorage.setItem('pageTutor', currentPage);
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

        rowsPerPageDropdown.addEventListener(
            'change', function () {
                rowsPerPage = parseInt(rowsPerPageDropdown.value);
                sessionStorage.setItem("rowsCountTutor", String(rowsPerPage));
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