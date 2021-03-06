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

mix.webpackConfig({ node: { fs: "empty" } });

mix.options({
  postCss: [
    require("autoprefixer")({
      browsers: ["last 40 versions"]
    })
  ],
  uglify: {}
});

mix.extract();

if (process.env.NODE_ENV === "production") {
  mix.webpackConfig({
    module: {
      rules: [
        {
          test: /\.jsx?$/,
          exclude: [],
          use: {
            loader: "babel-loader",
            options: {
              presets: ["@babel/preset-env"],
              compact: true
            }
          }
        }
      ]
    }
  });
} else {
  mix.webpackConfig({
    devtool: "inline-source-map"
  });
}

mix
  .js("resources/assets/js/app.js", "public/js")
  .js("resources/assets/js/print.js", "public/js")
  .version();

mix
  .sass("resources/assets/sass/app.scss", "public/css")
  .sass("resources/assets/sass/print.scss", "public/css")
  // .sourceMaps(true)
  .version();

/*
mix.browserSync({
  proxy: "goprep.localhost"
});
*/

// mix.disableNotifications();

mix.copyDirectory("resources/assets/images", "public/images");
