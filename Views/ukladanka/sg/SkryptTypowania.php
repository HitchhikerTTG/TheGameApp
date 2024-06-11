<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.betting-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            const url = this.action;

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const newTypText = data.newTypText;
                    const accordionItem = this.closest('.accordion-item');
                    const button = accordionItem.querySelector('.accordion-button');
                    const oldText = button.innerText;
                    const newText = oldText.replace(/Twój typ: [^|]+|Wytypuj/, newTypText);
                    button.innerText = newText;

                    button.style.backgroundColor = 'lightgreen';
                    setTimeout(() => { button.style.backgroundColor = ''; }, 1000);
                } else {
                    alert('Błąd przy zapisywaniu danych');
                }
            })
            .catch(error => {
                console.error('Wystąpił błąd: ', error);
            });
        });
    });

    document.querySelectorAll('.golden-game-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const gameId = this.dataset.gameId;
            const allCheckboxes = document.querySelectorAll('.golden-game-checkbox');
            
            allCheckboxes.forEach(cb => {
                if (cb.dataset.gameId !== gameId) {
                    cb.disabled = this.checked;
                }
            });

            const button = document.querySelector(`#collapse${gameId}`).closest('.accordion-item').querySelector('.accordion-button');
            if (this.checked) {
                button.classList.add('golden-header');
            } else {
                button.classList.remove('golden-header');
            }
        });

        if (checkbox.checked) {
            document.querySelector(`#collapse${checkbox.dataset.gameId}`).closest('.accordion-item').querySelector('.accordion-button').classList.add('golden-header');
        }
    });

    document.querySelectorAll('.plus').forEach(button => {
        button.addEventListener('click', function() {
            const scoreDisplay = this.closest('.team').querySelector('.score-display');
            const scoreValue = this.closest('.team').querySelector('.score-value');
            let currentVal = parseInt(scoreDisplay.textContent) || 0;
            currentVal++;
            scoreDisplay.textContent = currentVal;
            scoreValue.value = currentVal;
        });
    });

    document.querySelectorAll('.minus').forEach(button => {
        button.addEventListener('click', function() {
            const scoreDisplay = this.closest('.team').querySelector('.score-display');
            const scoreValue = this.closest('.team').querySelector('.score-value');
            let currentVal = parseInt(scoreDisplay.textContent) || 0;
            if (currentVal > 0) {
                currentVal--;
            }
            scoreDisplay.textContent = currentVal;
            scoreValue.value = currentVal;
        });
    });
});
</script>