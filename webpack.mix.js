const mix = require("laravel-mix");

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.options({
  postCss: [
    require("autoprefixer")({
      browsers: ["last 40 versions"]
    })
  ]
});

mix
  .js("resources/assets/js/app.js", "public/js")
  .version()
  .sourceMaps(true)
  .version();

mix
  .sass("resources/assets/sass/app.scss", "public/css")
  .sass("resources/assets/sass/print.scss", "public/css")
  .sourceMaps(true)
  .version();

/*
mix.browserSync({
  proxy: "goprep.localhost"
});
*/

// mix.disableNotifications();

mix.copyDirectory("resources/assets/images", "public/images");
