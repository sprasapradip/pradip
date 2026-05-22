
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



document.addEventListener("DOMContentLoaded", function () {

    /* ================= MOBILE SIDEBAR ================= */
    const menuToggle = document.getElementById("menuToggle");
    const sidebar = document.getElementById("sidebar");

    if (menuToggle && sidebar) {
        menuToggle.addEventListener("click", function () {
            sidebar.classList.toggle("show");
        });
    }

    /* ================= SETTINGS TOGGLE ================= */
    const settingsToggle = document.getElementById("settingsToggle");
    const settingsGroup = document.getElementById("settingsGroup");

    if (settingsToggle && settingsGroup) {
        settingsToggle.addEventListener("click", function (e) {
            e.preventDefault();
            settingsGroup.classList.toggle("open");
        });
    }

    /* ================= PROFILE DROPDOWN ================= */
    const profile = document.getElementById("profileBox");
    const avatar = document.getElementById("avatarBtn");

    if (profile && avatar) {

        avatar.addEventListener("click", function (e) {
            e.stopPropagation();
            profile.classList.toggle("active");
        });

        document.addEventListener("click", function (e) {
            if (!profile.contains(e.target)) {
                profile.classList.remove("active");
            }
        });
    }

    /* ================= SEARCH FILTER ================= */
    const search = document.getElementById("adminSearch");

    if (search) {
        search.addEventListener("input", function () {

            let value = this.value.toLowerCase();

            document.querySelectorAll(".nav a").forEach(function (item) {

                let text = item.textContent.toLowerCase();

                item.style.display = text.includes(value) ? "flex" : "none";
            });
        });
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



document.addEventListener("DOMContentLoaded", function () {

    const profile = document.getElementById("profileBox");
    const avatar = document.getElementById("avatarBtn");

    if (!profile || !avatar) return;

    // TOGGLE ON CLICK
    avatar.addEventListener("click", function (e) {
        e.stopPropagation();
        profile.classList.toggle("active");
    });

    // CLOSE ONLY WHEN CLICK OUTSIDE
    document.addEventListener("click", function (e) {
        if (!profile.contains(e.target)) {
            profile.classList.remove("active");
        }
    });

});


document.addEventListener("DOMContentLoaded", function () {

    const search = document.getElementById("adminSearch");

    if (!search) return;

    search.addEventListener("keyup", function () {

        let value = this.value.toLowerCase();

        document.querySelectorAll(".nav a").forEach(function (item) {

            let text = item.textContent.toLowerCase();

            if (text.includes(value)) {
                item.style.display = "flex";
            } else {
                item.style.display = "none";
            }

        });

    });

});