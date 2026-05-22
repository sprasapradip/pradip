<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

document.addEventListener("DOMContentLoaded", function () {

    if (document.getElementById("editor")) {

        CKEDITOR.replace("editor", {
            height: 300,
            removeButtons: ""
        });

    }

});











// SCROLL REVEAL + GRID ACTIVATION
const elements = document.querySelectorAll(".reveal, .grid");

function animate(){
    const trigger = window.innerHeight - 80;

    elements.forEach(el=>{
        const top = el.getBoundingClientRect().top;

        if(top < trigger){
            el.classList.add("active");
        }
    });
}

window.addEventListener("scroll", animate);
window.addEventListener("load", animate);


// PARALLAX
window.addEventListener("scroll", ()=>{
    document.documentElement.style.setProperty('--scrollY', window.scrollY * 0.2 + "px");
});


// COUNTER ANIMATION
const counters = document.querySelectorAll(".counter");

counters.forEach(counter=>{
    let target = +counter.getAttribute("data-target");
    let count = 0;

    let update = ()=>{
        let increment = target / 100;

        if(count < target){
            count += increment;
            counter.innerText = Math.ceil(count);
            requestAnimationFrame(update);
        }else{
            counter.innerText = target;
        }
    };

    update();
});


// SKILL BAR FILL
const skills = document.querySelectorAll(".skill-fill");

function fillSkills(){
    skills.forEach(skill=>{
        let width = skill.getAttribute("data-width");
        skill.style.width = width;
    });
}

window.addEventListener("load", fillSkills);


// MAGNETIC BUTTON
document.querySelectorAll(".btn").forEach(btn=>{
    btn.addEventListener("mousemove", e=>{
        const rect = btn.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width/2;
        const y = e.clientY - rect.top - rect.height/2;

        btn.style.transform = `translate(${x*0.2}px, ${y*0.2}px)`;
    });

    btn.addEventListener("mouseleave", ()=>{
        btn.style.transform = "translate(0,0)";
    });
});


function openProjectModal(title, description, image){

    document.getElementById('modalTitle').innerHTML = title;

    document.getElementById('modalDescription').innerHTML = description;

    if(image){

        document.getElementById('modalImageWrap').innerHTML = `
            <img src="uploads/${image}"
                 style="
                    width:100%;
                    max-height:450px;
                    object-fit:cover;
                 ">
        `;

    } else {

        document.getElementById('modalImageWrap').innerHTML = '';
    }

    document.getElementById('projectModal').style.display = 'block';

    document.body.style.overflow = 'hidden';
}

function closeProjectModal(){

    document.getElementById('projectModal').style.display = 'none';

    document.body.style.overflow = 'auto';
}

// CLOSE ON OUTSIDE CLICK
window.onclick = function(event){

    let modal = document.getElementById('projectModal');

    if(event.target == modal){

        closeProjectModal();
    }
}