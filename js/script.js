var sideBarIsOpen = !0;
toggleBtn.addEventListener("click", (event) => {
    event.preventDefault();
    if (sideBarIsOpen) {
        dashboard_sidebar.style.transition = "0.4s all";
        dashboard_sidebar.style.width = "0";
        dashboard_content_container.style.width = "100%";
        dashboard_logo.style.visibility = "hidden";
        userImage.style.visibility = "hidden";
        document.querySelector(".dashboard_name").style.visibility = "hidden";
        document.querySelector(".dashboard_sidebar_user").style.borderBottom = "none";
        var menuItems = document.querySelectorAll(".liMainMenu, .subMenuContainer, .menuText, .showHideSubMenu, .iconArrow");
        menuItems.forEach(function (item) {
            item.style.visibility = "hidden";
            item.style.opacity = "0";
        });
        document.getElementsByClassName("dashboard_menu_lists")[0].style.textAlign = "center";
        sideBarIsOpen = !1;
    } else {
        dashboard_sidebar.style.width = "20%";
        dashboard_content_container.style.width = "80%";
        dashboard_logo.style.visibility = "visible";
        userImage.style.visibility = "visible";
        document.querySelector(".dashboard_name").style.visibility = "visible";
        document.querySelector(".dashboard_sidebar_user").style.borderBottom = "";
        var menuItems = document.querySelectorAll(".liMainMenu, .subMenuContainer, .menuText, .showHideSubMenu, .iconArrow");
        menuItems.forEach(function (item) {
            item.style.visibility = "visible";
            item.style.opacity = "0";
            item.style.transition = "opacity 2s ease";
            setTimeout(() => {
                item.style.opacity = "1";
            }, 5);
        });
        var sidebarMenus = document.querySelector(".dashboard_sidebar_menus");
        sidebarMenus.classList.add("visible");
        sidebarMenus.style.display = "block";
        setTimeout(() => {
            sidebarMenus.classList.remove("visible");
        }, 500);
        document.getElementsByClassName("dashboard_menu_lists")[0].style.textAlign = "left";
        sideBarIsOpen = !0;
    }
});
document.addEventListener("click", function (e) {
    let clickedEl = e.target;
    if (clickedEl.classList.contains("showHideSubMenu")) {
        let subMenu = clickedEl.closest("li").querySelector(".subMenus");
        let mainMenuIcon = clickedEl.closest("li").querySelector(".iconArrow");
        let subMenus = document.querySelectorAll(".subMenus");
        subMenus.forEach((sub) => {
            if (subMenu !== sub) sub.style.display = "none";
        });
        showHideSubMenu(subMenu, mainMenuIcon);
    }
});
function showHideSubMenu(subMenu, mainMenuIcon) {
    if (subMenu != null) {
        if (subMenu.style.display === "block") {
            subMenu.style.display = "none";
            mainMenuIcon.classList.remove("fa-angle-down");
            mainMenuIcon.classList.add("fa-angle-left");
        } else {
            subMenu.style.display = "block";
            mainMenuIcon.classList.remove("fa-angle-left");
            mainMenuIcon.classList.add("fa-angle-down");
        }
    }
}
let pathArray = window.location.pathname.split("/");
let curFile = pathArray[pathArray.length - 1];
let curNav = document.querySelector('a[href="./' + curFile + '"]');
curNav.classList.add("subMenuActive");
let mainNav = curNav.closest("li.liMainMenu");
mainNav.style.background = "rgb(194 ,194 ,194,0.45)";
mainNav.style.borderLeft = "5px solid rgb(102, 0, 0)";
let subMenu = curNav.closest(".subMenus");
let mainMenuIcon = mainNav.querySelector("i.iconArrow");
showHideSubMenu(subMenu, mainMenuIcon);
let lastClickedIcon = null;
function togglePopup(event, message) {
    const icon = event.target;
    const existingPopup = document.querySelector(".popup");
    if (existingPopup && lastClickedIcon === icon) {
        existingPopup.remove();
        lastClickedIcon = null;
        resetAddUserPosition();
        return;
    }
    if (existingPopup) {
        existingPopup.remove();
    }
    const popup = document.createElement("div");
    popup.className = "popup show";
    popup.textContent = message;
    document.body.appendChild(popup);
    const iconRect = icon.getBoundingClientRect();
    popup.style.left = `${iconRect.left}px`;
    popup.style.top = `${iconRect.bottom + 9}px`;
    lastClickedIcon = icon;
    document.addEventListener("click", function handleOutsideClick(e) {
        if (!popup.contains(e.target) && e.target !== icon) {
            popup.remove();
            lastClickedIcon = null;
            resetAddUserPosition();
            document.removeEventListener("click", handleOutsideClick);
        }
    });
    moveAddUserPosition();
}
function moveAddUserPosition() {
    const buttonContainer = document.querySelector(".button-container");
    if (buttonContainer) {
        const popupHeight = document.querySelector(".popup").offsetHeight + 10;
        buttonContainer.style.marginTop = `${popupHeight}px`;
    }
}
function resetAddUserPosition() {
    const buttonContainer = document.querySelector(".button-container");
    if (buttonContainer) {
        buttonContainer.style.marginTop = "";
    }
}
function selectRole(element) {
    const role = element.getAttribute("data-value");
    const roleInput = document.getElementById("role");
    roleInput.value = role;
    document.querySelectorAll(".permission .row div").forEach((div) => {
        div.classList.remove("active");
    });
    element.classList.add("active");
}
document.querySelectorAll(".info-icon").forEach((icon) => {
    icon.addEventListener("click", function (event) {
        event.stopPropagation();
    });
});
