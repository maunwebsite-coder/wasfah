const initCountdowns = () => {
    const countdownNodes = document.querySelectorAll('[data-countdown]');

    if (!countdownNodes.length) {
        return;
    }

    const updateNode = (node) => {
        const end = node.getAttribute('data-end');

        if (!end) {
            return;
        }

        const target = new Date(end).getTime();
        const output = node.querySelector('strong');

        if (!output || Number.isNaN(target)) {
            return;
        }

        const tick = () => {
            const now = Date.now();
            const diff = target - now;

            if (diff <= 0) {
                output.textContent = 'Offer ended';
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
            const minutes = Math.floor((diff / (1000 * 60)) % 60);
            const seconds = Math.floor((diff / 1000) % 60);

            output.textContent = `${days}d ${hours}h ${minutes}m ${seconds}s`;
            requestAnimationFrame(() => {
                window.setTimeout(tick, 1000);
            });
        };

        tick();
    };

    countdownNodes.forEach(updateNode);
};

const initFirstOrderPopup = () => {
    const popup = document.getElementById('first-order-popup');

    if (!popup) {
        return;
    }

    const key = 'thedolci-first-order-popup-closed';
    const closeButton = document.getElementById('close-first-order-popup');

    if (window.localStorage.getItem(key) === '1') {
        return;
    }

    window.setTimeout(() => {
        popup.classList.add('show');
        popup.setAttribute('aria-hidden', 'false');
    }, 1200);

    const close = () => {
        popup.classList.remove('show');
        popup.setAttribute('aria-hidden', 'true');
        window.localStorage.setItem(key, '1');
    };

    closeButton?.addEventListener('click', close);

    popup.addEventListener('click', (event) => {
        if (event.target === popup) {
            close();
        }
    });
};

const initProductGallery = () => {
    const main = document.getElementById('main-product-image');

    if (!main) {
        return;
    }

    const thumbs = document.querySelectorAll('[data-product-thumb]');

    const setActiveThumb = (activeThumb) => {
        thumbs.forEach((thumb) => {
            thumb.classList.toggle('is-active', thumb === activeThumb);
        });
    };

    if (thumbs.length) {
        setActiveThumb(thumbs[0]);
    }

    thumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            const image = thumb.getAttribute('data-image');

            if (image) {
                main.src = image;
                setActiveThumb(thumb);
            }
        });
    });
};

const initDynamicPrice = () => {
    const priceTarget = document.querySelector('[data-dynamic-price]');

    if (!priceTarget) {
        return;
    }

    const sizeOptions = document.querySelectorAll('[data-size-option]');
    const pepperOption = document.querySelector('[data-pepper-option]');
    const packagingSelect = document.querySelector('[data-packaging-select]');
    const quantityInput = document.querySelector('[data-quantity-input]');

    const updatePrice = () => {
        const selectedSize = document.querySelector('[data-size-option]:checked');

        if (!selectedSize) {
            return;
        }

        const sizePrice = Number(selectedSize.getAttribute('data-price') || 0);
        const pepperPrice = pepperOption?.checked ? Number(pepperOption.getAttribute('data-price') || 0) : 0;

        let packagingPrice = 0;
        if (packagingSelect && packagingSelect.selectedIndex >= 0) {
            const selectedPackaging = packagingSelect.options[packagingSelect.selectedIndex];
            packagingPrice = Number(selectedPackaging?.getAttribute('data-price') || 0);
        }

        const quantity = Math.max(1, Number(quantityInput?.value || 1));
        const total = (sizePrice + pepperPrice + packagingPrice) * quantity;
        priceTarget.textContent = `JOD ${total.toFixed(2)}`;
    };

    sizeOptions.forEach((option) => {
        option.addEventListener('change', updatePrice);
    });

    pepperOption?.addEventListener('change', updatePrice);
    packagingSelect?.addEventListener('change', updatePrice);
    quantityInput?.addEventListener('input', updatePrice);

    updatePrice();
};

const initHeaderMenu = () => {
    const header = document.querySelector('[data-dolci-topbar]');

    if (!header) {
        return;
    }

    const menuToggle = header.querySelector('[data-dolci-menu-toggle]');
    const mobileDrawer = header.querySelector('[data-dolci-mobile-drawer]');

    if (!menuToggle || !mobileDrawer) {
        return;
    }

    const closeControls = header.querySelectorAll('[data-dolci-mobile-close]');
    const drawerLinks = mobileDrawer.querySelectorAll('a');

    const setOpen = (open) => {
        mobileDrawer.classList.toggle('is-open', open);
        mobileDrawer.setAttribute('aria-hidden', open ? 'false' : 'true');
        menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.classList.toggle('dolci-lock-scroll', open);
    };

    menuToggle.addEventListener('click', () => {
        const isOpen = mobileDrawer.classList.contains('is-open');
        setOpen(!isOpen);
    });

    closeControls.forEach((control) => {
        control.addEventListener('click', () => {
            setOpen(false);
        });
    });

    drawerLinks.forEach((link) => {
        link.addEventListener('click', () => {
            setOpen(false);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 1023) {
            setOpen(false);
        }
    });
};

const initScrollSliderWithDots = ({ sliderSelector, slideSelector, dotSelector }) => {
    const slider = document.querySelector(sliderSelector);

    if (!slider) {
        return;
    }

    const slides = Array.from(slider.querySelectorAll(slideSelector));
    const dots = Array.from(document.querySelectorAll(dotSelector));

    if (!slides.length || !dots.length) {
        return;
    }

    const setActiveDot = (index) => {
        dots.forEach((dot, dotIndex) => {
            const isActive = dotIndex === index;
            dot.classList.toggle('is-active', isActive);
            dot.setAttribute('aria-current', isActive ? 'true' : 'false');
        });
    };

    let scrollUpdateTimer = null;

    const updateActiveFromScroll = () => {
        const sliderLeft = slider.getBoundingClientRect().left;
        let activeIndex = 0;
        let minDistance = Number.POSITIVE_INFINITY;

        slides.forEach((slide, index) => {
            const distance = Math.abs(slide.getBoundingClientRect().left - sliderLeft);

            if (distance < minDistance) {
                minDistance = distance;
                activeIndex = index;
            }
        });

        setActiveDot(activeIndex);
    };

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            slides[index]?.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'start',
            });
        });
    });

    slider.addEventListener(
        'scroll',
        () => {
            if (scrollUpdateTimer) {
                window.clearTimeout(scrollUpdateTimer);
            }

            scrollUpdateTimer = window.setTimeout(updateActiveFromScroll, 60);
        },
        { passive: true }
    );

    window.addEventListener('resize', updateActiveFromScroll);
    updateActiveFromScroll();
};

const initTrustSlider = () => {
    initScrollSliderWithDots({
        sliderSelector: '[data-trust-slider]',
        slideSelector: '[data-trust-slide]',
        dotSelector: '[data-trust-dot]',
    });
};

const initInstagramSlider = () => {
    initScrollSliderWithDots({
        sliderSelector: '[data-instagram-slider]',
        slideSelector: '[data-instagram-slide]',
        dotSelector: '[data-instagram-dot]',
    });
};

const initAdminInstagramEditor = () => {
    const section = document.querySelector('[data-instagram-admin]');

    if (!section) {
        return;
    }

    const list = section.querySelector('[data-instagram-list]');
    const addButton = section.querySelector('[data-instagram-add]');
    const template = section.querySelector('[data-instagram-template]');
    const countTarget = section.querySelector('[data-instagram-count]');
    const maxPosts = Number(section.getAttribute('data-max-posts') || 12);
    const isDisabled = section.getAttribute('data-disabled') === '1';

    if (!list || !template) {
        return;
    }

    const updatePreview = (item) => {
        const imageInput = item.querySelector('[data-instagram-image]');
        const preview = item.querySelector('[data-instagram-preview]');
        const placeholder = item.querySelector('[data-instagram-placeholder]');

        if (!imageInput || !preview || !placeholder) {
            return;
        }

        const imageUrl = imageInput.value.trim();
        const hasImage = imageUrl !== '';

        preview.src = imageUrl;
        preview.hidden = !hasImage;
        placeholder.hidden = hasImage;
    };

    const bindItem = (item) => {
        const imageInput = item.querySelector('[data-instagram-image]');
        const removeButton = item.querySelector('[data-instagram-remove]');

        imageInput?.addEventListener('input', () => updatePreview(item));
        removeButton?.addEventListener('click', () => {
            item.remove();
            reindexItems();
        });

        updatePreview(item);
    };

    const reindexItems = () => {
        const items = Array.from(list.querySelectorAll('[data-instagram-item]'));

        if (items.length === 0) {
            addInstagramPost();
            return;
        }

        items.forEach((item, index) => {
            item.setAttribute('data-index', String(index));

            const title = item.querySelector('[data-instagram-item-title]');
            if (title) {
                title.textContent = `Post ${index + 1}`;
            }

            item.querySelectorAll('[data-field]').forEach((field) => {
                const key = field.getAttribute('data-field');

                if (key) {
                    field.setAttribute('name', `instagram_posts[${index}][${key}]`);
                }
            });
        });

        const canAdd = !isDisabled && items.length < maxPosts;
        if (addButton) {
            addButton.disabled = !canAdd;
        }

        items.forEach((item) => {
            const removeButton = item.querySelector('[data-instagram-remove]');
            if (removeButton) {
                removeButton.disabled = isDisabled || items.length <= 1;
            }
        });

        if (countTarget) {
            countTarget.textContent = String(items.length);
        }
    };

    const addInstagramPost = () => {
        const itemCount = list.querySelectorAll('[data-instagram-item]').length;

        if (itemCount >= maxPosts) {
            return;
        }

        const baseItem = template.content.firstElementChild;

        if (!baseItem) {
            return;
        }

        const newItem = baseItem.cloneNode(true);
        list.appendChild(newItem);
        bindItem(newItem);
        reindexItems();
    };

    Array.from(list.querySelectorAll('[data-instagram-item]')).forEach(bindItem);

    addButton?.addEventListener('click', addInstagramPost);

    reindexItems();
};

document.addEventListener('DOMContentLoaded', () => {
    initHeaderMenu();
    initTrustSlider();
    initInstagramSlider();
    initAdminInstagramEditor();
    initCountdowns();
    initFirstOrderPopup();
    initProductGallery();
    initDynamicPrice();
});
