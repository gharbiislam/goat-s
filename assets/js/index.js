
document.getElementById('type-select').addEventListener('change', function(e) {
    var selectedType = e.target.value;

    if (selectedType === 'alimentaire') {
        document.getElementById('alim').classList.remove('d-none');
        document.getElementById('v-sport').classList.add('d-none'); 
    } else if (selectedType === 'vetement') {
        document.getElementById('v-sport').classList.remove('d-none');
        document.getElementById('alim').classList.add('d-none'); 
    }
});