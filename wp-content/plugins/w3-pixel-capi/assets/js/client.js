(function() {
  var url = new URL(window.location.href);
  var fbclid = url.searchParams.get("fbclid");
  if (fbclid) {
    document.cookie = "_fbc=fb.1." + Date.now() + "." + fbclid + "; path=/";
  }
})();
