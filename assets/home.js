const searchForm = document.querySelector("[data-category-search]");

if (searchForm instanceof HTMLFormElement) {
    const searchInput = searchForm.querySelector('input[type="search"]');
    const categories = Array.from(document.querySelectorAll("[data-search-item]"));
    const emptyMessage = document.querySelector("[data-search-empty]");
    const statusMessage = document.querySelector("[data-search-status]");

    if (searchInput instanceof HTMLInputElement && emptyMessage instanceof HTMLElement) {
        const normalize = (value) => value
            .toLocaleLowerCase("vi")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .replace(/đ/g, "d");

        const filterCategories = () => {
            const query = normalize(searchInput.value.trim());
            let visibleCount = 0;

            categories.forEach((category) => {
                const terms = normalize(category.dataset.searchTerms || category.textContent || "");
                const isMatch = query === "" || terms.includes(query);
                category.hidden = !isMatch;

                if (isMatch) {
                    visibleCount += 1;
                }
            });

            emptyMessage.hidden = visibleCount > 0;

            if (statusMessage instanceof HTMLElement) {
                statusMessage.textContent = query === ""
                    ? ""
                    : visibleCount === 1
                        ? "Tìm thấy 1 danh mục"
                        : `Tìm thấy ${visibleCount} danh mục`;
            }
        };

        searchInput.addEventListener("input", filterCategories);
        searchForm.addEventListener("submit", (event) => {
            event.preventDefault();
            filterCategories();
            document.querySelector("#shop-products")?.scrollIntoView({ behavior: "smooth" });
        });
    }
}
