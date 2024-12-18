document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('select');
    var instances = M.FormSelect.init(elems);

    // Attendre un moment avant de mettre à jour les options
    setTimeout(function() {
        updateSelectOptions();
    }, 500);
});

function updateSelectOptions() {
    var selectElements = document.querySelectorAll('select');
    selectElements.forEach(select => {
        var selectInstance = M.FormSelect.getInstance(select);
        if (selectInstance) {
            selectInstance.update();
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.tooltipped');
    var instances = M.Tooltip.init(elems, options);
});

$(document).ready(function(){
    $('.tooltipped').tooltip();
});