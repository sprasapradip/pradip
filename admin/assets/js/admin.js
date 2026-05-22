
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');

function openSidebar(){
    sidebar.classList.add('show');
    document.body.classList.add('sidebar-open');
}

function closeSidebar(){
    sidebar.classList.remove('show');
    document.body.classList.remove('sidebar-open');
}

if(menuToggle){

    menuToggle.addEventListener('click', (e) => {
        e.stopPropagation();

        if(sidebar.classList.contains('show')){
            closeSidebar();
        }else{
            openSidebar();
        }
    });

    document.addEventListener('click', () => {
        closeSidebar();
    });

    sidebar.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    sidebar.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            closeSidebar();
        });
    });

}

/* SIDEBAR TOGGLE */
document.getElementById("menuToggle").onclick = function(){
    document.getElementById("sidebar").classList.toggle("show");
};

/* DARK MODE */
document.getElementById("darkToggle").onclick = function(){
    document.body.classList.toggle("dark");
    localStorage.setItem("dark", document.body.classList.contains("dark"));
};

/* LOAD DARK MODE */
if(localStorage.getItem("dark") === "true"){
    document.body.classList.add("dark");
}


document.addEventListener("click", function(e){

    // close all dropdowns when clicking outside
    document.querySelectorAll(".actions").forEach(el => {
        if(!el.contains(e.target)){
            el.classList.remove("active");
        }
    });

    // toggle dropdown when clicking button
    if(e.target.classList.contains("action-btn")){

        let parent = e.target.closest(".actions");

        parent.classList.toggle("active");
    }

});




<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

    CKEDITOR.replace('editor', {
        height: 300,
        removeButtons: '',
    });

function previewImage(event){
    let reader = new FileReader();
    reader.onload = function(){
        let img = document.getElementById('preview');
        img.src = reader.result;
        img.style.display = 'block';
    }
    reader.readAsDataURL(event.target.files[0]);
}








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

document.addEventListener("DOMContentLoaded", function () {

    const toggle = document.getElementById("settingsToggle");
    const group = document.getElementById("settingsGroup");

    if (!toggle || !group) return;

    toggle.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        group.classList.toggle("open");
    });

});