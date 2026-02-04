module.exports = {
  content: ["./**/*.php", "./**/*.html", "./**/*.js", "!./node_modules/**/*"],
  theme: {
    extend: {
      colors: {
        brand: { dark: "#141416", accent: "#BE0000" },
      },
      fontFamily: { libertinus: ["Libertinus", "sans-serif"] },
      maxHeight: { screen: "100vh" },
      opacity: { 0: "0", 100: "1" },
    },
  },
  plugins: [],
};
