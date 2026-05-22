// ================= PROFILE DROPDOWN =================
const profile = document.getElementById("profileBox");
const avatar = document.getElementById("avatarBtn");

if (profile && avatar) {

    avatar.addEventListener("click", function (e) {
        e.stopPropagation();
        profile.classList.toggle("active");
    });

    // keep menu open when interacting inside
    const menu = profile.querySelector(".profile-menu");

    if (menu) {
        menu.addEventListener("click", function (e) {
            e.stopPropagation();
        });
    }

    document.addEventListener("click", function (e) {
        if (!profile.contains(e.target)) {
            profile.classList.remove("active");
        }
    });
}


// ================= SETTINGS DROPDOWN =================
const settingsToggle = document.getElementById("settingsToggle");
const settingsGroup = document.getElementById("settingsGroup");

if (settingsToggle && settingsGroup) {

    settingsToggle.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        settingsGroup.classList.toggle("open");
    });

    document.addEventListener("click", function (e) {
        if (!settingsGroup.contains(e.target)) {
            settingsGroup.classList.remove("open");
        }
    });
}