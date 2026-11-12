document.addEventListener("DOMContentLoaded", () => {

    // 1. Navigation Scrolled State
    const nav = document.querySelector("nav");

    if (nav) {
        window.addEventListener("scroll", () => {
            if (window.scrollY > 50) {
                nav.classList.add("scrolled");
            } else {
                nav.classList.remove("scrolled");
            }
        });
    }

    // 2. Mobile Menu Toggle
    const menuToggle = document.querySelector(".menu-toggle");
    const navLinks = document.querySelector(".nav-links");

    if (menuToggle && navLinks) {
        menuToggle.addEventListener("click", () => {
            navLinks.classList.toggle("active");
            menuToggle.innerHTML = navLinks.classList.contains("active") ? "✕" : "☰";
        });

        // Tutup menu saat link diklik (mobile)
        navLinks.querySelectorAll("a").forEach(link => {
            link.addEventListener("click", () => {
                navLinks.classList.remove("active");
                menuToggle.innerHTML = "☰";
            });
        });
    }

    // 3. Active Nav Link (Polished)
    const currentPath = window.location.pathname.split("/").pop() || "index.html";
    document.querySelectorAll(".nav-links > li").forEach(li => {
        const parentLink = li.querySelector("a");
        const allLinks = li.querySelectorAll("a");
        let isActive = false;

        allLinks.forEach(link => {
            const href = link.getAttribute("href");
            if (href) {
                const linkPath = href.split("/").pop().split("#")[0];
                if (linkPath === currentPath) {
                    link.classList.add("active-link");
                    isActive = true;
                }
            }
        });

        if (isActive && parentLink) {
            parentLink.classList.add("active-link");
        }
    });

    // 4. Intersection Observer for Fade-In Elements
    const fadeElements = document.querySelectorAll(".fade-in-element");

    if (fadeElements.length > 0) {
        if ('IntersectionObserver' in window) {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: "0px 0px -50px 0px"
            };

            const fadeObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("visible");
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            fadeElements.forEach(el => fadeObserver.observe(el));
        } else {
            fadeElements.forEach(el => el.classList.add("visible"));
        }
    }

    // 5. Album Carousel Logic
    const carouselTrack = document.querySelector(".carousel-track");
    const prevBtn = document.querySelector(".prev-btn");
    const nextBtn = document.querySelector(".next-btn");
    const dots = document.querySelectorAll(".carousel-dot");

    if (carouselTrack) {
        const getCardWidth = () => {
            const card = carouselTrack.querySelector(".carousel-card");
            if (card) {
                const style = window.getComputedStyle(card);
                const marginRight = parseFloat(style.marginRight) || 0;
                const marginLeft = parseFloat(style.marginLeft) || 0;
                return card.offsetWidth + 24; // 24px is the flex gap
            }
            return 300;
        };

        const updateActiveDot = () => {
            const scrollLeft = carouselTrack.scrollLeft;
            const maxScroll = carouselTrack.scrollWidth - carouselTrack.clientWidth;
            let activeIndex = 0;
            
            if (maxScroll > 0) {
                const scrollPercent = scrollLeft / maxScroll;
                activeIndex = Math.round(scrollPercent * (dots.length - 1));
            }
            
            dots.forEach((dot, idx) => {
                if (idx === activeIndex) {
                    dot.classList.add("active");
                } else {
                    dot.classList.remove("active");
                }
            });
        };

        // Scroll track on button click
        if (prevBtn && nextBtn) {
            nextBtn.addEventListener("click", () => {
                const cardWidth = getCardWidth();
                carouselTrack.scrollBy({ left: cardWidth, behavior: "smooth" });
            });

            prevBtn.addEventListener("click", () => {
                const cardWidth = getCardWidth();
                carouselTrack.scrollBy({ left: -cardWidth, behavior: "smooth" });
            });
        }

        // Add click events to dots
        dots.forEach((dot, idx) => {
            dot.addEventListener("click", () => {
                const maxScroll = carouselTrack.scrollWidth - carouselTrack.clientWidth;
                if (maxScroll > 0) {
                    const targetScroll = (idx / (dots.length - 1)) * maxScroll;
                    carouselTrack.scrollTo({ left: targetScroll, behavior: "smooth" });
                }
            });
        });

        // Track scroll position to update dots
        let scrollTimeout;
        carouselTrack.addEventListener("scroll", () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(updateActiveDot, 50);
        });

        // Initialize active dot on load
        updateActiveDot();

        // Recalculate on window resize to ensure correct tracking
        window.addEventListener("resize", updateActiveDot);
    }

    // 6. Checkout Modal Logic (dipakai oleh toko & tiket)
    const modal = document.getElementById("checkout-modal");
    if (modal) {
        const buyButtons = document.querySelectorAll(".buy-btn");
        const closeBtn = modal.querySelector(".close-btn");
        const checkoutForm = document.getElementById("checkout-form");
        const checkoutFormContainer = document.getElementById("checkout-form-container");
        const checkoutSuccess = document.getElementById("checkout-success");
        const successEmail = document.getElementById("success-email");
        const successCloseBtn = document.getElementById("success-close-btn");

        const modalProductTitle = document.getElementById("modal-product-title");
        const modalProductPrice = document.getElementById("modal-product-price");

        // Field yang nantinya dikirim ke backend (tabel orders / order_items / customers)
        const itemTypeInput = document.getElementById("checkout-item-type");
        const productIdInput = document.getElementById("checkout-product-id");
        const ticketIdInput = document.getElementById("checkout-concert-ticket-id");
        const quantityInput = document.getElementById("checkout-quantity");
        const addressGroup = document.getElementById("checkout-address-group");
        const addressInput = document.getElementById("checkout-address");
        const createAccount = document.getElementById("checkout-create-account");
        const passwordGroup = document.getElementById("checkout-password-group");
        const passwordInput = document.getElementById("checkout-password");

        const packageGroup = document.getElementById("checkout-package-group");
        const packageContext = document.getElementById("checkout-package-context");
        const packageRadios = packageGroup ? packageGroup.querySelectorAll(".package-radio") : [];
        const standardPackageOption = packageGroup ? packageGroup.querySelector('[data-package="standard"]') : null;
        const vipPackageOption = packageGroup ? packageGroup.querySelector('[data-package="vip"]') : null;
        const standardPackageRadio = standardPackageOption ? standardPackageOption.querySelector(".package-radio") : null;
        const vipPackageRadio = vipPackageOption ? vipPackageOption.querySelector(".package-radio") : null;

        let activeTicketPackages = null;
        let unitPrice = 0;
        const formatPrice = (value) => "$" + value.toFixed(2);
        const updateTotal = () => {
            const qty = Math.max(1, parseInt(quantityInput.value, 10) || 1);
            modalProductPrice.textContent = formatPrice(unitPrice * qty);
        };

        // Alamat pengiriman hanya wajib untuk barang fisik (orders.shipping_address NULL untuk tiket)
        const setItemType = (type) => {
            const isTicket = type === "ticket";
            addressGroup.style.display = isTicket ? "none" : "block";
            addressInput.required = !isTicket;
        };

        // Password hanya muncul bila pembeli memilih membuat akun (customers.password)
        const togglePassword = () => {
            const on = createAccount.checked;
            passwordGroup.style.display = on ? "block" : "none";
            passwordInput.required = on;
            if (!on) passwordInput.value = "";
        };

        const getSelectedPackageKey = () => {
            const checked = packageGroup ? packageGroup.querySelector(".package-radio:checked") : null;
            return checked ? checked.value : "standard";
        };

        const syncPackageChoice = () => {
            if (!packageGroup || !activeTicketPackages) return;

            const key = getSelectedPackageKey();
            const chosen = activeTicketPackages[key];

            packageGroup.querySelectorAll(".package-option").forEach(option => {
                option.classList.toggle("selected", option.getAttribute("data-package") === key);
            });

            if (!chosen) return;

            unitPrice = parseFloat(chosen.price) || 0;
            ticketIdInput.value = chosen.ticketId;
            modalProductTitle.textContent = chosen.name;
            updateTotal();
        };

        const configureTicketPackages = (packages) => {
            if (!packageGroup) return;

            activeTicketPackages = packages;
            const hasVip = Boolean(packages.vip.ticketId);

            packageContext.textContent = [packages.city, packages.event].filter(Boolean).join(" · ");
            standardPackageOption.querySelector(".package-option-price").textContent =
                formatPrice(parseFloat(packages.standard.price) || 0);
            vipPackageOption.querySelector(".package-option-price").textContent =
                hasVip ? formatPrice(parseFloat(packages.vip.price) || 0) : "--";
            vipPackageOption.querySelector(".package-option-badge").textContent =
                hasVip ? "Official VIP" : "Not Available";
            vipPackageOption.querySelector(".package-option-desc").textContent = hasVip
                ? "Early entry, signed poster, tote bag, laminate & more."
                : "VIP package is not offered for this show.";

            standardPackageRadio.disabled = !packages.standard.ticketId;
            vipPackageRadio.disabled = !hasVip;
            standardPackageOption.classList.toggle("disabled", !packages.standard.ticketId);
            vipPackageOption.classList.toggle("disabled", !hasVip);

            standardPackageRadio.checked = true;
            packageGroup.style.display = "block";
            syncPackageChoice();
        };

        const openCheckout = ({ itemType, productId, ticketId, name, price, packages }) => {
            const type = itemType || "product";
            unitPrice = parseFloat(price) || 0;

            checkoutForm.reset();
            itemTypeInput.value = type;
            productIdInput.value = productId || "";
            ticketIdInput.value = ticketId || "";
            modalProductTitle.textContent = name || "";
            setItemType(type);
            togglePassword();

            if (packageGroup) {
                if (packages) {
                    configureTicketPackages(packages);
                } else {
                    activeTicketPackages = null;
                    packageGroup.style.display = "none";
                }
            }

            updateTotal();

            checkoutFormContainer.style.display = "block";
            checkoutSuccess.style.display = "none";

            modal.classList.add("active");
            document.body.style.overflow = "hidden"; // Prevent background scroll
        };

        buyButtons.forEach(button => {
            button.addEventListener("click", () => {
                openCheckout({
                    itemType: button.getAttribute("data-item-type"),
                    productId: button.getAttribute("data-product-id"),
                    ticketId: button.getAttribute("data-concert-ticket-id"),
                    name: button.getAttribute("data-name"),
                    price: button.getAttribute("data-price")
                });
            });
        });

        quantityInput.addEventListener("input", updateTotal);
        createAccount.addEventListener("change", togglePassword);

        // Function to close modal
        const closeModal = () => {
            modal.classList.remove("active");
            document.body.style.overflow = "";
        };

        closeBtn.addEventListener("click", closeModal);
        successCloseBtn.addEventListener("click", closeModal);

        // Close on clicking outside modal content
        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Pilihan paket tiket di dalam checkout
        if (packageGroup) {
            packageRadios.forEach(radio => {
                radio.addEventListener("change", syncPackageChoice);
            });
        }

        document.querySelectorAll(".ticket-select-btn").forEach(button => {
            button.addEventListener("click", () => {
                const packages = {
                    itemType: button.getAttribute("data-item-type") || "ticket",
                    city: button.getAttribute("data-city") || "",
                    event: button.getAttribute("data-event") || "",
                    standard: {
                        ticketId: button.getAttribute("data-standard-ticket-id") || "",
                        name: button.getAttribute("data-standard-name") || "",
                        price: button.getAttribute("data-standard-price") || ""
                    },
                    vip: {
                        ticketId: button.getAttribute("data-vip-ticket-id") || "",
                        name: button.getAttribute("data-vip-name") || "",
                        price: button.getAttribute("data-vip-price") || ""
                    }
                };

                openCheckout({
                    itemType: packages.itemType,
                    ticketId: packages.standard.ticketId,
                    name: packages.standard.name,
                    price: packages.standard.price,
                    packages
                });
            });
        });

        // Handle form submit (tahap backend: kirim FormData ke endpoint pembuatan pesanan)
        checkoutForm.addEventListener("submit", (e) => {
            e.preventDefault();
            successEmail.textContent = document.getElementById("checkout-email").value;

            checkoutFormContainer.style.display = "none";
            checkoutSuccess.style.display = "block";
        });
    }

    // 7. Contact Page Fan Message Form
    const contactForm = document.getElementById("contact-form");
    if (contactForm) {
        const contactSuccess = document.getElementById("contact-form-success");
        const contactSuccessEmail = document.getElementById("contact-success-email");
        const contactSuccessCloseBtn = document.getElementById("contact-success-close-btn");

        contactForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const emailInput = document.getElementById("contact-email").value;
            contactSuccessEmail.textContent = emailInput;

            contactForm.style.display = "none";
            contactSuccess.style.display = "block";
        });

        if (contactSuccessCloseBtn) {
            contactSuccessCloseBtn.addEventListener("click", () => {
                contactForm.reset();
                contactForm.style.display = "block";
                contactSuccess.style.display = "none";
            });
        }
    }

    // 8. Mode show-only saat datang dari schedule
    const showSlug = new URLSearchParams(window.location.search).get("show");
    const showCard = showSlug ? document.getElementById(showSlug) : null;
    const validShowCard = showCard && showCard.classList.contains("ticket-card") ? showCard : null;
    const hashTargetId = window.location.hash.replace("#", "");
    const hashTarget = hashTargetId ? document.getElementById(hashTargetId) : null;

    if (validShowCard) {
        document.querySelectorAll(".ticket-card").forEach(card => {
            if (card !== validShowCard) card.style.display = "none";
        });

        const availabilityTitle = document.getElementById("ticket-availability-title");
        const availabilitySubtitle = document.getElementById("ticket-availability-subtitle");
        const viewAllLink = document.getElementById("ticket-view-all");
        const cityName = validShowCard.querySelector(".ticket-city");
        const eventDetail = validShowCard.querySelector(".ticket-detail");

        if (availabilityTitle && cityName) {
            availabilityTitle.textContent = cityName.textContent.toUpperCase();
        }
        if (availabilitySubtitle && eventDetail) {
            availabilitySubtitle.textContent = eventDetail.textContent;
        }
        if (viewAllLink) {
            viewAllLink.style.display = "inline-block";
        }
    }

    const highlightTarget = validShowCard || hashTarget;

    if (highlightTarget && highlightTarget.classList.contains("ticket-card")) {
        requestAnimationFrame(() => {
            highlightTarget.scrollIntoView({ behavior: "smooth", block: "start" });
            highlightTarget.classList.add("is-highlighted");

            window.setTimeout(() => {
                highlightTarget.classList.remove("is-highlighted");
            }, 5000);
        });
    }

    // 9. Filter kategori untuk galeri foto dan toko
    document.querySelectorAll("[data-category-filter]").forEach(filterBar => {
        const targetSelector = filterBar.getAttribute("data-target");
        const grid = targetSelector ? document.querySelector(targetSelector) : null;
        if (!grid) return;

        const items = Array.from(grid.querySelectorAll("[data-category]"));
        const buttons = Array.from(filterBar.querySelectorAll("[data-filter]"));
        const emptyState = filterBar.closest("section")?.querySelector("[data-filter-empty]");

        const counts = { all: items.length };
        items.forEach(item => {
            const category = item.getAttribute("data-category");
            counts[category] = (counts[category] || 0) + 1;
        });

        buttons.forEach(button => {
            const count = counts[button.getAttribute("data-filter")] || 0;
            const countLabel = button.querySelector(".filter-count");
            if (countLabel) countLabel.textContent = "(" + count + ")";
        });

        const applyFilter = (filter, updateUrl) => {
            const activeFilter = buttons.some(button => button.getAttribute("data-filter") === filter)
                ? filter
                : "all";
            let visibleCount = 0;

            buttons.forEach(button => {
                const isActive = button.getAttribute("data-filter") === activeFilter;
                button.classList.toggle("is-active", isActive);
                button.setAttribute("aria-pressed", isActive ? "true" : "false");
            });

            items.forEach(item => {
                const isVisible = activeFilter === "all" || item.getAttribute("data-category") === activeFilter;
                item.classList.toggle("is-filtered-out", !isVisible);
                if (isVisible) visibleCount++;
            });

            if (emptyState) {
                emptyState.classList.toggle("is-visible", visibleCount === 0);
            }

            if (updateUrl) {
                if (activeFilter === "all") {
                    window.history.replaceState(null, "", window.location.href.split("#")[0]);
                } else {
                    window.history.replaceState(null, "", "#" + activeFilter);
                }
            }
        };

        applyFilter(window.location.hash.replace("#", ""), false);

        buttons.forEach(button => {
            button.addEventListener("click", () => {
                applyFilter(button.getAttribute("data-filter"), true);
            });
        });

        window.addEventListener("hashchange", () => {
            applyFilter(window.location.hash.replace("#", ""), false);
        });
    });

    // 10. Modal lirik lengkap
    const lyricsModal = document.getElementById("lyrics-modal");

    if (lyricsModal) {
        const lyricBlocks = Array.from(document.querySelectorAll(".lyric-block[data-lyrics-id]"));
        const lyricsTitle = document.getElementById("lyrics-modal-title");
        const lyricsMeta = document.getElementById("lyrics-modal-meta");
        const lyricsBody = document.getElementById("lyrics-modal-body");
        const lyricsGenius = document.getElementById("lyrics-modal-genius");
        const lyricsExplicit = document.getElementById("lyrics-modal-explicit");
        const lyricsClose = document.getElementById("lyrics-modal-close");
        let lastLyricsTrigger = null;

        const getFocusableElements = () => Array.from(
            lyricsModal.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])')
        ).filter(element => element.getClientRects().length > 0);

        const openLyrics = (block) => {
            const template = document.getElementById(block.getAttribute("data-lyrics-id"));
            if (!template) return;

            lyricsTitle.textContent = block.getAttribute("data-title") || "";
            lyricsMeta.textContent = [block.getAttribute("data-album"), block.getAttribute("data-year")]
                .filter(Boolean)
                .join(" · ");
            lyricsBody.innerHTML = template.innerHTML;
            lyricsBody.scrollTop = 0;
            lyricsGenius.href = block.getAttribute("data-genius-url") || "https://genius.com";
            lyricsExplicit.hidden = block.getAttribute("data-explicit") !== "true";

            lastLyricsTrigger = block;
            lyricsModal.classList.add("active");
            lyricsModal.setAttribute("aria-hidden", "false");
            document.body.classList.add("lyrics-modal-open");
            requestAnimationFrame(() => {
                if (lyricsModal.classList.contains("active")) lyricsClose.focus();
            });
        };

        const closeLyrics = () => {
            lyricsModal.classList.remove("active");
            lyricsModal.setAttribute("aria-hidden", "true");
            document.body.classList.remove("lyrics-modal-open");

            if (lastLyricsTrigger) {
                lastLyricsTrigger.focus();
                lastLyricsTrigger = null;
            }
        };

        lyricBlocks.forEach(block => {
            block.addEventListener("click", () => openLyrics(block));
            block.addEventListener("keydown", (event) => {
                if (event.key === "Enter" || event.key === " ") {
                    event.preventDefault();
                    openLyrics(block);
                }
            });
        });

        lyricsClose.addEventListener("click", closeLyrics);

        lyricsModal.addEventListener("click", (event) => {
            if (event.target === lyricsModal) closeLyrics();
        });

        document.addEventListener("keydown", (event) => {
            if (!lyricsModal.classList.contains("active")) return;

            if (event.key === "Escape") {
                event.preventDefault();
                closeLyrics();
                return;
            }

            if (event.key !== "Tab") return;

            const focusable = getFocusableElements();
            if (focusable.length === 0) return;

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    }

});