document.querySelectorAll(".year-section").forEach(section => {

    const track = section.querySelector("[data-slider]");
    const viewport = section.querySelector(".gallery-viewport");
    const next = section.querySelector("[data-next]");
    const prev = section.querySelector("[data-prev]");
    const dotsWrap = section.querySelector("[data-dots]");

    const images = track.querySelectorAll("img");
    const visibleImages = 4;
    let currentIndex = 0;

    updateButtons();

    function updateButtons(){
        prev.disabled = currentIndex === 0;
        next.disabled = currentIndex >= images.length - visibleImages;
    }

    function updateSlider() {
        const imageWidth = images[0].offsetWidth;
        const gap = 20;
        const distance = currentIndex * (imageWidth + gap);
        track.style.transform = `translateX(-${distance}px)`;
        updateButtons();
    }

    next.addEventListener("click", () => {
        if(currentIndex < images.length - visibleImages){
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

    /* --- puntini di paginazione (mobile) --- */
    if (dotsWrap) {
        const totalPages = Math.max(1, Math.round(track.scrollWidth / viewport.clientWidth));

        for (let i = 0; i < totalPages; i++) {
            const dot = document.createElement("span");
            dot.className = "gallery-dot";
            if (i === 0) dot.classList.add("is-active");
            dotsWrap.appendChild(dot);
        }

        const dots = dotsWrap.querySelectorAll(".gallery-dot");

        viewport.addEventListener("scroll", () => {
            const page = Math.round(viewport.scrollLeft / viewport.clientWidth);
            dots.forEach((dot, i) => dot.classList.toggle("is-active", i === page));
        });
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
