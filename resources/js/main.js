document.querySelectorAll(".gallery-container").forEach(section => {

    const track = section.querySelector("[data-slider]");
    const viewport = section.querySelector(".gallery-viewport");
    const next = section.querySelector("[data-next]");
    const prev = section.querySelector("[data-prev]");
    const dotsWrap = section.nextElementSibling && section.nextElementSibling.matches(".gallery-dots")
        ? section.nextElementSibling
        : null;

    const images = track.querySelectorAll("img");
    const gap = 20;
    let currentIndex = 0;

    updateButtons();

    function maxIndex(){
        return Math.max(0, images.length - 1);
    }

    function updateButtons(){
        prev.disabled = currentIndex === 0;
        next.disabled = currentIndex >= maxIndex();
    }

    function updateDots() {
        if (!dotsWrap) return;
        const dots = dotsWrap.querySelectorAll(".gallery-dot");
        const step = track.children[0] ? track.children[0].offsetWidth + gap : 1;
        const isMobile = window.matchMedia("(max-width: 575.98px)").matches;
        const active = isMobile
            ? Math.max(0, Math.min(dots.length - 1, Math.round(viewport.scrollLeft / step)))
            : currentIndex;
        dots.forEach((dot, i) => {
            dot.classList.toggle("is-active", i === active);
        });
    }

    function updateSlider() {
        const cardWidth = track.children[0].offsetWidth;
        const distance = currentIndex * (cardWidth + gap);
        track.style.transform = `translateX(-${distance}px)`;
        updateButtons();
        updateDots();
    }

    next.addEventListener("click", () => {
        if(currentIndex < maxIndex()){
            currentIndex++;
            updateSlider();
        }
    });

    prev.addEventListener("click", () => {
        if(currentIndex > 0){
            currentIndex--;
            updateSlider();
        }
    });

    /* --- puntini di paginazione (uno per album, mobile e desktop) --- */
    if (dotsWrap) {
        const mqMobile = window.matchMedia("(max-width: 575.98px)");

        function buildDots() {
            const totalPages = Math.max(1, images.length);

            dotsWrap.innerHTML = "";

            for (let i = 0; i < totalPages; i++) {
                const dot = document.createElement("span");
                dot.className = "gallery-dot";
                dot.setAttribute("role", "button");
                dot.setAttribute("tabindex", "0");
                dot.setAttribute("aria-label", `Vai all'album ${i + 1} di ${totalPages}`);
                dot.addEventListener("click", () => {
                    if (mqMobile.matches) {
                        const step = track.children[0].offsetWidth + gap;
                        viewport.scrollTo({ left: i * step, behavior: "smooth" });
                    } else {
                        currentIndex = i;
                        updateSlider();
                    }
                });
                dot.addEventListener("keydown", (e) => {
                    if (e.key === "Enter" || e.key === " ") {
                        e.preventDefault();
                        dot.click();
                    }
                });
                dotsWrap.appendChild(dot);
            }

            updateDots();
        }

        buildDots();

        mqMobile.addEventListener("change", () => {
            currentIndex = Math.min(currentIndex, maxIndex());
            buildDots();
            updateSlider();
        });

        viewport.addEventListener("scroll", updateDots, { passive: true });
    }

    /* --- piccolo "nudge" per far capire che si può scorrere (mobile) --- */
    if (window.matchMedia("(max-width: 575.98px)").matches) {
        setTimeout(() => {
            viewport.scrollTo({ left: 36, behavior: "smooth" });
            setTimeout(() => {
                viewport.scrollTo({ left: 0, behavior: "smooth" });
            }, 450);
        }, 600);
    }

});

/*==================================
LIGHTBOX
==================================*/

const galleryImages = document.querySelectorAll(".lightbox-trigger");

const lightbox = document.getElementById("lightbox");

if (lightbox) {
const lightboxImg = lightbox.querySelector(".lightbox-image");

const closeBtn = lightbox.querySelector(".lightbox-close");

const nextBtn = lightbox.querySelector(".lightbox-next");

const prevBtn = lightbox.querySelector(".lightbox-prev");

let current = 0;

function openLightbox(index){

    current = index;

    lightboxImg.src = galleryImages[current].src;

    lightboxImg.alt = galleryImages[current].alt;

    lightbox.classList.add("open");

    lightbox.setAttribute("aria-hidden", "false");

    document.body.style.overflow = "hidden";

}

function closeLightbox(){

    lightbox.classList.remove("open");

    lightbox.setAttribute("aria-hidden", "true");

    document.body.style.overflow = "";

}

function nextImage(){

    current++;

    if(current >= galleryImages.length){

        current = 0;

    }

    lightboxImg.src = galleryImages[current].src;

    lightboxImg.alt = galleryImages[current].alt;

}

function prevImage(){

    current--;

    if(current < 0){

        current = galleryImages.length - 1;

    }

    lightboxImg.src = galleryImages[current].src;

    lightboxImg.alt = galleryImages[current].alt;

}

galleryImages.forEach((img,index)=>{

    img.addEventListener("click",()=>{

        openLightbox(index);

    });

    img.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            openLightbox(index);
        }
    });

});

closeBtn.addEventListener("click",closeLightbox);

nextBtn.addEventListener("click",nextImage);

prevBtn.addEventListener("click",prevImage);



document.addEventListener("keydown",(e)=>{

    if(!lightbox.classList.contains("open")) return;

    if(e.key==="Escape") closeLightbox();

    if(e.key==="ArrowRight") nextImage();

    if(e.key==="ArrowLeft") prevImage();

});
}

/*==================================
TOAST
==================================*/

const toastStack = (() => {
    let stack = document.querySelector("[data-toast-stack]");
    if (!stack) {
        stack = document.createElement("div");
        stack.className = "toast-stack";
        stack.setAttribute("data-toast-stack", "");
        stack.setAttribute("aria-live", "polite");
        document.body.appendChild(stack);
    }
    return stack;
})();

const TOAST_ICONS = {
    success: "fa-solid fa-circle-check",
    error: "fa-solid fa-circle-exclamation",
};

function showToast(message, type = "success") {
    const toast = document.createElement("div");
    toast.className = `toast toast--${type}`;
    toast.setAttribute("role", type === "error" ? "alert" : "status");

    toast.innerHTML = `
        <span class="toast-icon"><i class="${TOAST_ICONS[type]}" aria-hidden="true"></i></span>
        <p class="toast-message"></p>
        <button class="toast-close" type="button" aria-label="Chiudi">&times;</button>
    `;
    toast.querySelector(".toast-message").textContent = message;

    toastStack.appendChild(toast);

    const remove = () => {
        if (toast.dataset.leaving) return;
        toast.dataset.leaving = "true";
        toast.classList.add("toast--leaving");
        toast.addEventListener("animationend", () => toast.remove(), { once: true });
        setTimeout(() => toast.remove(), 400);
    };

    toast.querySelector(".toast-close").addEventListener("click", remove);
    setTimeout(remove, type === "error" ? 8000 : 5000);
}

/*==================================
CONTACT FORM (AJAX)
==================================*/

const contactForm = document.querySelector("[data-contact-form]");

if (contactForm) {
    function clearFieldErrors() {
        contactForm.querySelectorAll(".contact-form-field-error").forEach(span => span.remove());
    }

    function setFieldErrors(errors) {
        for (const key of Object.keys(errors)) {
            const input = contactForm.querySelector(`[name="${key}"]`);
            if (!input || !errors[key].length) continue;

            const span = document.createElement("span");
            span.className = "contact-form-field-error";
            span.textContent = errors[key][0];
            input.insertAdjacentElement("afterend", span);
        }
    }

    contactForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        clearFieldErrors();

        const button = contactForm.querySelector("button[type='submit']");
        button.disabled = true;

        try {
            const response = await fetch(contactForm.action, {
                method: "POST",
                headers: { Accept: "application/json" },
                body: new FormData(contactForm),
            });

            const data = await response.json().catch(() => ({}));

            if (response.ok) {
                contactForm.reset();
                showToast("Grazie per il tuo messaggio. Ti risponderò al più presto.", "success");
            } else if (response.status === 422) {
                setFieldErrors(data.errors || {});
                showToast("Impossibile inviare il messaggio: controlla i campi segnalati.", "error");
            } else {
                showToast("Invio non riuscito. Riprova più tardi.", "error");
            }
        } catch {
            showToast("Invio non riuscito. Riprova più tardi.", "error");
        } finally {
            button.disabled = false;
        }
    });
}

/*==================================
IMMAGINI — REVEAL PROGRESSIVO
==================================*/

document.querySelectorAll("[data-reveal]").forEach((target, index) => {
    const img = target.matches("img") ? target : target.querySelector("img");

    if (!img) {
        target.classList.add("is-loaded");
        return;
    }

    target.style.setProperty("--reveal-delay", `${Math.min(index * 60, 900)}ms`);

    const reveal = () => target.classList.add("is-loaded");

    if (img.complete && img.naturalWidth > 0) {
        requestAnimationFrame(() => requestAnimationFrame(reveal));
    } else {
        img.addEventListener("load", reveal, { once: true });
        img.addEventListener("error", reveal, { once: true });
        setTimeout(reveal, 4000);
    }
});
