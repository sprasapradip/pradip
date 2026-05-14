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
</body>
</html>