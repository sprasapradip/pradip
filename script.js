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