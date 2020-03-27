function image(x) {
    if (x.matches) {
        document.getElementById("image").style.display = "none";
    } else {
        document.getElementById("image").style.display = "inline";
      
    }
  }
  var x = window.matchMedia("(max-width: 800px)");
  image(x);
  x.addListener(image);