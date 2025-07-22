module.exports = {
    proxy: "127.0.0.1:8000", // Sesuaikan dengan URL Laravel Anda
    files: [
      "resources/views/**/*.blade.php",
      "public/css/**/*.css",
      "public/js/**/*.js",
    ],
    notify: false,
    open: false,
  };