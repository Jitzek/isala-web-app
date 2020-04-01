function sidebar(x) {
  if (x.matches) {
      document.getElementById("sidebarCollapse").style.display = "inline";
    $("#sidebarCollapse").on("click", function() {
      $("#sidebar").toggleClass("active");
    });
  } else {
    $("#sidebarCollapse").off();
    document.getElementById("sidebarCollapse").style.display = "none"
  }
}
var x = window.matchMedia("(max-width: 768px)");
sidebar(x);
x.addListener(sidebar);

