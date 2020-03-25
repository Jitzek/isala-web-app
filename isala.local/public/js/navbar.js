function sidebar(x) {
  if (x.matches) {
    document.getElementsByClassName("sidebarCollapse")[0].style.display = "inline";
    $("#sidebarCollapse").on("click", function () {
      $("#sidebar").toggleClass("active");
    });
  } else {
    $("#sidebarCollapse").off();
    if (document.getElementById('sidebar').className == "active") {
      $("#sidebar").toggleClass("active");
    }
    document.getElementsByClassName("sidebarCollapse")[0].style.display = "none"
  }
}
var x = window.matchMedia("(max-width: 768px)");
sidebar(x);
x.addListener(sidebar);