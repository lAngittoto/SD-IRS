
    function openPopup(id) {
        const popup = document.getElementById(id);
        popup.classList.remove('hidden');
        popup.classList.add('flex');
    }

    function closePopup(id) {
        const popup = document.getElementById(id);
        popup.classList.remove('flex');
        popup.classList.add('hidden');
    }

