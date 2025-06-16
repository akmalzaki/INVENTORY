var sideBarIsOpen = true;

toggleBtn.addEventListener('click', (event) => {
    event.preventDefault();

    if (sideBarIsOpen) {
        // Menyembunyikan sidebar
        dashboard_sidebar.style.transition = '0.4s all';
        dashboard_sidebar.style.width = '0'; // Menghilangkan sidebar
        dashboard_content_container.style.width = '100%'; // Konten menjadi penuh

        // Menyembunyikan elemen logo, gambar user, nama user, dan semua menu
        dashboard_logo.style.visibility = 'hidden'; // Sembunyikan tulisan "IMS"
        userImage.style.visibility = 'hidden'; // Sembunyikan gambar user
        document.querySelector('.dashboard_name').style.visibility = 'hidden'; // Sembunyikan nama user

        // Menghilangkan border bottom pada dashboard_sidebar_user
        document.querySelector('.dashboard_sidebar_user').style.borderBottom = 'none'; // Menghilangkan border bottom

        // Sembunyikan semua menu dan ikon
        var menuItems = document.querySelectorAll('.liMainMenu, .subMenuContainer, .menuText, .showHideSubMenu, .iconArrow');
        menuItems.forEach(function(item) {
            item.style.visibility = 'hidden'; // Menyembunyikan semua menu dan ikon
            item.style.opacity = '0'; // Mulai dengan opacity 0
        });

        document.getElementsByClassName('dashboard_menu_lists')[0].style.textAlign = 'center'; // Mengatur teks menu ke tengah
        sideBarIsOpen = false; // Menandakan sidebar sudah ditutup
    } else {
        // Menampilkan kembali sidebar
        dashboard_sidebar.style.width = '20%'; // Lebar sidebar
        dashboard_content_container.style.width = '80%'; // Konten menjadi 80%

        // Menampilkan elemen logo, gambar user, dan nama user
        dashboard_logo.style.visibility = 'visible'; // Tampilkan tulisan "IMS"
        userImage.style.visibility = 'visible'; // Tampilkan gambar user
        document.querySelector('.dashboard_name').style.visibility = 'visible'; // Tampilkan nama user

        // Mengembalikan border bottom pada dashboard_sidebar_user
        document.querySelector('.dashboard_sidebar_user').style.borderBottom = ''; // Mengembalikan border bottom ke semula

        // Menampilkan semua menu dan ikon
        var menuItems = document.querySelectorAll('.liMainMenu, .subMenuContainer, .menuText, .showHideSubMenu, .iconArrow');
        menuItems.forEach(function(item) {
            item.style.visibility = 'visible'; // Menampilkan semua menu dan ikon
            item.style.opacity = '0'; // Mulai dengan opacity 0
            item.style.transition = 'opacity 2s ease'; // Transisi untuk opacity
            setTimeout(() => {
                item.style.opacity = '1'; // Fade in
            }, 5); // Delay sedikit untuk memastikan transisi
        });

        // Menambahkan kelas visible untuk sidebar
        var sidebarMenus = document.querySelector('.dashboard_sidebar_menus');
        sidebarMenus.classList.add('visible');
        sidebarMenus.style.display = 'block'; // Tampilkan elemen

        // Menghapus kelas visible setelah beberapa waktu untuk menghindari masalah bertumpuk
        setTimeout(() => {
            sidebarMenus.classList.remove('visible');
        }, 500); // Waktu yang sama dengan durasi transisi
       
        document.getElementsByClassName('dashboard_menu_lists')[0].style.textAlign = 'left'; // Mengatur teks menu ke kiri
        sideBarIsOpen = true; // Menandakan sidebar sudah dibuka
    }
});
// submenu show / hide function
// function toggleSubMenu(element) {
//     var parentLi = element.closest('.liMainMenu');
//     var subMenu = parentLi.querySelector('.subMenus');
//     subMenu.classList.toggle('show');
// }

// Close the submenu if the user clicks outside of it
// window.onclick = function(e) {
//     if (!e.target.closest('.liMainMenu')) {
//         var subMenus = document.querySelectorAll('.subMenus');
//         subMenus.forEach(function(subMenu) {
//             if (subMenu.classList.contains('show')) {
//                 subMenu.classList.remove('show');
//             }
//         });
//     }
// };

// Submenu Show/Hide function
document.addEventListener('click', function(e){
    let clickedEl = e.target;

    if(clickedEl.classList.contains('showHideSubMenu')){
        let subMenu = clickedEl.closest('li').querySelector('.subMenus');
        let mainMenuIcon = clickedEl.closest('li').querySelector('.iconArrow');

        // Close open submenus
        let subMenus = document.querySelectorAll('.subMenus');
        subMenus.forEach((sub) => {
            if(subMenu !== sub) sub.style.display = 'none';
        });

        // Call function to hide/show submenu
        showHideSubMenu(subMenu, mainMenuIcon);
    }
});

// Function - to show/hide submenu
function showHideSubMenu(subMenu, mainMenuIcon){
    //Check if there is submenu
    if(subMenu !=  null){
        if(subMenu.style.display === 'block'){
            subMenu.style.display = 'none';
            mainMenuIcon.classList.remove('fa-angle-down');
            mainMenuIcon.classList.add('fa-angle-left');
        } else {
            subMenu.style.display = 'block';
            mainMenuIcon.classList.remove('fa-angle-left');
            mainMenuIcon.classList.add('fa-angle-down');
        }
    }
}

// Add/Hide active class to menu 
// Get the current page
// Use selector to get the current menu or submenu
// Add the active class

let pathArray = window.location.pathname.split('/');
let curFile = pathArray[pathArray.length - 1];

let curNav = document.querySelector('a[href="./'+ curFile+'"]');
curNav.classList.add('subMenuActive');

let mainNav = curNav.closest('li.liMainMenu');
mainNav.style.background = 'rgb(194 ,194 ,194,0.45)';
mainNav.style.borderLeft = '5px solid rgb(102, 0, 0)';

let subMenu = curNav.closest('.subMenus');
let mainMenuIcon = mainNav.querySelector('i.iconArrow');

// Call function to hide/show submenu
showHideSubMenu(subMenu, mainMenuIcon);


// icon role info
let lastClickedIcon = null; // Variabel untuk melacak ikon terakhir yang diklik

function togglePopup(event, message) {
    const icon = event.target; // Ikon yang diklik
    const existingPopup = document.querySelector('.popup');

    // Jika ikon yang sama di-klik, hapus popup
    if (existingPopup && lastClickedIcon === icon) {
        existingPopup.remove();
        lastClickedIcon = null; // Reset ikon terakhir
        resetAddUserPosition(); // Kembalikan posisi Add User
        return;
    }

    // Hapus popup lama (jika ada dan berasal dari ikon lain)
    if (existingPopup) {
        existingPopup.remove();
    }

    // Buat popup baru
    const popup = document.createElement('div');
    popup.className = 'popup show';
    popup.textContent = message;



    // Tambahkan popup ke body
    document.body.appendChild(popup);

    // Atur posisi popup berdasarkan posisi ikon
    const iconRect = icon.getBoundingClientRect();
    popup.style.left = `${iconRect.left}px`;
    popup.style.top = `${iconRect.bottom + 9}px`;

    // Perbarui ikon terakhir yang diklik
    lastClickedIcon = icon;

    // Tambahkan event listener untuk klik di luar popup
    document.addEventListener('click', function handleOutsideClick(e) {
        if (!popup.contains(e.target) && e.target !== icon) {
            popup.remove();
            lastClickedIcon = null; // Reset ikon terakhir
            resetAddUserPosition(); // Kembalikan posisi Add User
            document.removeEventListener('click', handleOutsideClick);
        }
    });

    // Pindahkan "Add User" ke bawah
    moveAddUserPosition();
}

// Fungsi untuk memindahkan posisi "Add User" ke bawah
function moveAddUserPosition() {
    const buttonContainer = document.querySelector('.button-container');
    if (buttonContainer) {
        const popupHeight = document.querySelector('.popup').offsetHeight + 10; // Tambahkan jarak ekstra
        buttonContainer.style.marginTop = `${popupHeight}px`;
    }
}

// Fungsi untuk mengembalikan posisi "Add User" kembali semula
function resetAddUserPosition() {
    const buttonContainer = document.querySelector('.button-container');
    if (buttonContainer) {
        buttonContainer.style.marginTop = ''; // Reset marginTop
    }
}

//role
function selectRole(element) {
    const role = element.getAttribute('data-value'); // Ambil value role dari atribut data-value
    const roleInput = document.getElementById('role');
    roleInput.value = role; // Set nilai role pada input hidden
    
    // Mengubah gaya visual untuk menunjukkan pilihan aktif
    document.querySelectorAll('.permission .row div').forEach((div) => {
        div.classList.remove('active'); // Menghapus kelas aktif dari semua elemen
    });
    element.classList.add('active'); // Menambahkan kelas aktif pada elemen yang dipilih
}

// Menambahkan event listener untuk menampilkan dan menyembunyikan popup
document.querySelectorAll('.info-icon').forEach((icon) => {
    icon.addEventListener('click', function(event) {
        event.stopPropagation(); // Menghindari klik ganda yang tidak perlu
    });
});





