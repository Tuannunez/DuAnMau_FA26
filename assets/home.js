const searchForm = document.querySelector("[data-category-search]");

const hero = document.querySelector("#banner");

if (hero instanceof HTMLElement) {
    const slides = Array.from(hero.querySelectorAll(".shop-hero-image"));
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

    if (slides.length > 1 && !reducedMotion.matches) {
        const transitionDurationMs = 3200;
        let currentSlide = 0;
        let timerId;

        const scheduleNextSlide = () => {
            timerId = window.setTimeout(() => {
                slides[currentSlide].classList.remove("is-active");
                slides[currentSlide].setAttribute("aria-hidden", "true");
                currentSlide = (currentSlide + 1) % slides.length;
                slides[currentSlide].classList.add("is-active");
                slides[currentSlide].removeAttribute("aria-hidden");

                timerId = window.setTimeout(scheduleNextSlide, transitionDurationMs);
            }, 4000);
        };

        scheduleNextSlide();
    }
}

const productGallery = document.querySelector("[data-product-gallery]");
if (productGallery instanceof HTMLElement) {
    const mainImage = productGallery.querySelector("[data-main-product-image]");
    const thumbnails = Array.from(productGallery.querySelectorAll("[data-product-thumbnail]"));

    if (mainImage instanceof HTMLImageElement) {
        thumbnails.forEach((thumbnail) => {
            thumbnail.addEventListener("click", () => {
                const imageSource = thumbnail.dataset.imageSrc;
                if (!imageSource) {
                    return;
                }

                mainImage.src = imageSource;
                thumbnails.forEach((item) => {
                    const isActive = item === thumbnail;
                    item.classList.toggle("is-active", isActive);
                    item.setAttribute("aria-pressed", String(isActive));
                });
            });
        });
    }
}

if (searchForm instanceof HTMLFormElement) {
    const searchInput = searchForm.querySelector('input[type="search"]');
    const products = Array.from(document.querySelectorAll("[data-product-card]"));
    const emptyMessage = document.querySelector("[data-search-empty]");
    const statusMessage = document.querySelector("[data-search-status]");

    if (searchInput instanceof HTMLInputElement && products.length > 0) {
        const normalize = (value) => value
            .toLocaleLowerCase("vi")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .replace(/đ/g, "d");

        const filterProducts = () => {
            const query = normalize(searchInput.value.trim());
            let visibleCount = 0;

            products.forEach((product) => {
                const terms = normalize(product.dataset.searchTerms || product.textContent || "");
                const isMatch = query === "" || terms.includes(query);
                product.hidden = !isMatch;

                if (isMatch) {
                    visibleCount += 1;
                }
            });

            if (emptyMessage instanceof HTMLElement) {
                emptyMessage.hidden = visibleCount > 0;
            }

            if (statusMessage instanceof HTMLElement) {
                statusMessage.textContent = query === ""
                    ? ""
                    : visibleCount === 1
                        ? "Tìm thấy 1 sản phẩm"
                        : `Tìm thấy ${visibleCount} sản phẩm`;
            }
        };

        searchInput.addEventListener("input", filterProducts);
        searchForm.addEventListener("submit", (event) => {
            event.preventDefault();
            filterProducts();
            document.querySelector("#shop-products")?.scrollIntoView({ behavior: "smooth" });
        });
    }
}
