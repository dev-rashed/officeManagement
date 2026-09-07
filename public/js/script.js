const header = document.querySelector("[data-header]");
const navToggle = document.querySelector("[data-nav-toggle]");
const navPanel = document.querySelector("[data-nav-panel]");
const navLinks = document.querySelectorAll("[data-nav-panel] a");
const hashNavLinks = document.querySelectorAll('[data-nav-panel] a[href^="#"]');
const revealItems = document.querySelectorAll(".reveal");
const sections = document.querySelectorAll("main section[id]");
const quoteForm = document.querySelector("[data-quote-form]");

function setHeaderState() {
    header?.classList.toggle("is-scrolled", window.scrollY > 12);
}

function closeNavigation() {
    document.body.classList.remove("nav-open");
    navToggle?.classList.remove("is-open");
    navPanel?.classList.remove("is-open");
    navToggle?.setAttribute("aria-expanded", "false");
    navToggle?.setAttribute("aria-label", "Open navigation");
}

function toggleNavigation() {
    const isOpen = navToggle?.classList.toggle("is-open");
    navPanel?.classList.toggle("is-open", isOpen);
    document.body.classList.toggle("nav-open", isOpen);
    navToggle?.setAttribute("aria-expanded", String(Boolean(isOpen)));
    navToggle?.setAttribute("aria-label", isOpen ? "Close navigation" : "Open navigation");
}

function updateActiveLink() {
    if (!hashNavLinks.length) {
        return;
    }

    let current = "";

    sections.forEach((section) => {
        const top = section.offsetTop - 130;

        if (window.scrollY >= top) {
            current = section.id;
        }
    });

    hashNavLinks.forEach((link) => {
        link.classList.toggle("is-active", link.getAttribute("href") === `#${current}`);
    });
}

navToggle?.addEventListener("click", toggleNavigation);

navLinks.forEach((link) => {
    link.addEventListener("click", closeNavigation);
});

quoteForm?.addEventListener("submit", (event) => {
    event.preventDefault();

    const data = new FormData(quoteForm);
    const name = data.get("name") || "";
    const email = data.get("email") || "";
    const project = data.get("project") || "";
    const budget = data.get("budget") || "";
    const message = data.get("message") || "";

    const body = [
        `Name: ${name}`,
        `Email: ${email}`,
        `Project type: ${project}`,
        `Budget range: ${budget}`,
        "",
        "Project notes:",
        message
    ].join("\n");

    window.location.href = `mailto:hello@hashtag.com?subject=${encodeURIComponent("Project quote request")}&body=${encodeURIComponent(body)}`;
});

document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
        closeNavigation();
    }
});

window.addEventListener("scroll", () => {
    setHeaderState();
    updateActiveLink();
}, { passive: true });

if ("IntersectionObserver" in window) {
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add("is-visible");
                revealObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.14,
        rootMargin: "0px 0px -40px 0px"
    });

    revealItems.forEach((item) => revealObserver.observe(item));
} else {
    revealItems.forEach((item) => item.classList.add("is-visible"));
}

setHeaderState();
updateActiveLink();
