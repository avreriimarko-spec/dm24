const startingWith = (subject, replacement) => ({ name, value }) => {
    if (name.startsWith(subject)) {
        name = name.replace(subject, replacement);
    }
    return { name, value };
};

Alpine.prefix('data-x-');
Alpine.mapAttributes(startingWith("data-x-on-", Alpine.prefixed("on:")));
Alpine.mapAttributes(
    startingWith("data-x-bind-", Alpine.prefixed("bind:"))
);
Alpine.mapAttributes(startingWith("data-x-cloak", "x-cloak"));

document.addEventListener("alpine:init", () => {
    document.querySelectorAll("[data-x-cloak]").forEach(el => el.removeAttribute("data-x-cloak"));
});



window.Alpine = Alpine

Alpine.start()

