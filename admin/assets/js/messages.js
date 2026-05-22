

/* ================= ACTION MENU ================= */

document.querySelectorAll('.action-btn').forEach(btn => {

    btn.addEventListener('click', function(e){

        e.stopPropagation();

        document.querySelectorAll('.actions').forEach(menu => {

            if(menu !== this.closest('.actions')){
                menu.classList.remove('active');
            }

        });

        this.closest('.actions').classList.toggle('active');

    });

});

/* CLOSE MENU */
document.addEventListener('click', function(){

    document.querySelectorAll('.actions').forEach(menu => {
        menu.classList.remove('active');
    });

});


/* ================= MODAL ================= */

const modal = document.getElementById('msgModal');

const closeModal = document.getElementById('closeModal');

const modalName = document.getElementById('modalName');

const modalEmail = document.getElementById('modalEmail');

const modalMessage = document.getElementById('modalMessage');


/* OPEN MODAL */

document.querySelectorAll('.view-btn').forEach(btn => {

    btn.addEventListener('click', function(e){

        e.stopPropagation();

        const name = this.dataset.name;
        const email = this.dataset.email;
        const message = this.dataset.message;

        modalName.textContent = name;

        modalEmail.textContent = email;

        modalMessage.innerHTML = message.replace(/\n/g, '<br>');

        modal.classList.add('active');

    });

});


/* CLOSE BUTTON */

closeModal.addEventListener('click', function(){

    modal.classList.remove('active');

});


/* OUTSIDE CLICK */

modal.addEventListener('click', function(e){

    if(e.target === modal){
        modal.classList.remove('active');
    }

});
