        </main>

    </div>

    <!-- =========================
         FOOTER
    ========================= -->

    <footer class="footer">

        © <?= date('Y') ?> Pradip Subedi Admin Panel

    </footer>

</div>
<script>
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
</script>








<script>
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
</script>









<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('editor', {
        height: 300,
        removeButtons: '',
    });
</script>
    <script>
function previewImage(event){
    let reader = new FileReader();
    reader.onload = function(){
        let img = document.getElementById('preview');
        img.src = reader.result;
        img.style.display = 'block';
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>

</body>
</html>