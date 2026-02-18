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
    const summaryTarget = section.querySelector('[data-instagram-summary]');
    const incompleteWarning = section.querySelector('[data-instagram-incomplete-warning]');
    const handleInput = section.querySelector('[data-instagram-handle]');
    const profileUrlInput = section.querySelector('[data-instagram-profile-url]');
    const profileLink = section.querySelector('[data-instagram-profile-link]');
    const syncProfileButton = section.querySelector('[data-instagram-sync-profile]');
    const maxPosts = Number(section.getAttribute('data-max-posts') || 12);
    const isDisabled = section.getAttribute('data-disabled') === '1';

    if (!list || !template) {
        return;
    }

    const boundItems = new WeakSet();

    const normalizeUrl = (value) => {
        const trimmed = String(value || '').trim();

        if (trimmed === '') {
            return '';
        }

        if (/^https?:\/\//i.test(trimmed)) {
            return trimmed;
        }

        if (/^\/\//.test(trimmed)) {
            return `https:${trimmed}`;
        }

        if (/^[\w.-]+\.[a-z]{2,}(?:\/.*)?$/i.test(trimmed)) {
            return `https://${trimmed}`;
        }

        return trimmed;
    };

    const normalizeHandle = (value) => {
        let cleaned = String(value || '').trim();

        if (cleaned === '') {
            return '';
        }

        const profileMatch = cleaned.match(/(?:instagram\.com|instagr\.am)\/@?([^/?#]+)/i);
        if (profileMatch && profileMatch[1]) {
            cleaned = profileMatch[1];
        }

        cleaned = cleaned.replace(/^@+/, '');
        cleaned = cleaned.replace(/\s+/g, '');
        cleaned = cleaned.replace(/\/+$/, '');

        if (cleaned.includes('/')) {
            [cleaned] = cleaned.split('/');
        }

        if (cleaned === '') {
            return '';
        }

        return `@${cleaned}`;
    };

    const buildProfileUrlFromHandle = (value) => {
        const normalizedHandle = normalizeHandle(value).replace(/^@/, '');

        if (normalizedHandle === '') {
            return '';
        }

        return `https://www.instagram.com/${normalizedHandle}/`;
    };

    const updateProfileLink = () => {
        let profileUrl = normalizeUrl(profileUrlInput?.value || '');

        if (profileUrl === '') {
            profileUrl = buildProfileUrlFromHandle(handleInput?.value || '');
        }

        if (profileLink) {
            profileLink.href = profileUrl || 'https://www.instagram.com/';
        }
    };

    const updateCaptionCount = (item) => {
        const captionInput = item.querySelector('[data-instagram-caption]');
        const counter = item.querySelector('[data-instagram-caption-count]');

        if (!captionInput || !counter) {
            return;
        }

        counter.textContent = String(captionInput.value.length);
    };

    const updatePostLink = (item) => {
        const postUrlInput = item.querySelector('[data-instagram-post-url]');
        const openPostLink = item.querySelector('[data-instagram-open-post]');

        if (!postUrlInput || !openPostLink) {
            return;
        }

        const normalizedUrl = normalizeUrl(postUrlInput.value);
        const hasUrl = normalizedUrl !== '';

        openPostLink.hidden = !hasUrl;

        if (hasUrl) {
            openPostLink.href = normalizedUrl;
        }
    };

    const updatePreview = (item) => {
        const imageInput = item.querySelector('[data-instagram-image]');
        const preview = item.querySelector('[data-instagram-preview]');
        const placeholder = item.querySelector('[data-instagram-placeholder]');
        const imageError = item.querySelector('[data-instagram-image-error]');

        if (!imageInput || !preview || !placeholder || !imageError) {
            return;
        }

        const imageUrl = normalizeUrl(imageInput.value);
        const hasImage = imageUrl !== '';

        imageError.hidden = true;

        if (!hasImage) {
            preview.hidden = true;
            placeholder.hidden = false;
            preview.removeAttribute('src');
            return;
        }

        if (preview.getAttribute('src') !== imageUrl) {
            preview.setAttribute('src', imageUrl);
        }

        preview.hidden = false;
        placeholder.hidden = true;
    };

    const getMissingFields = (item) => {
        const imageValue = item.querySelector('[data-field="image"]')?.value.trim() || '';
        const urlValue = item.querySelector('[data-field="url"]')?.value.trim() || '';
        const captionValue = item.querySelector('[data-field="caption"]')?.value.trim() || '';
        const missing = [];

        if (imageValue === '') {
            missing.push('image URL');
        }

        if (urlValue === '') {
            missing.push('post URL');
        }

        if (captionValue === '') {
            missing.push('caption');
        }

        return missing;
    };

    const updateItemState = (item) => {
        const missingFields = getMissingFields(item);
        const statusTarget = item.querySelector('[data-instagram-item-status]');
        const missingTarget = item.querySelector('[data-instagram-missing]');
        const isComplete = missingFields.length === 0;

        item.classList.toggle('is-incomplete', !isComplete);
        item.classList.toggle('is-complete', isComplete);

        if (statusTarget) {
            statusTarget.textContent = isComplete ? 'Ready' : 'Incomplete';
            statusTarget.classList.toggle('is-ready', isComplete);
            statusTarget.classList.toggle('is-incomplete', !isComplete);
        }

        if (missingTarget) {
            missingTarget.hidden = isComplete;

            if (!isComplete) {
                missingTarget.textContent = `Missing fields: ${missingFields.join(', ')}.`;
            }
        }

        return isComplete;
    };

    const refreshSummary = () => {
        const items = Array.from(list.querySelectorAll('[data-instagram-item]'));
        let readyCount = 0;

        items.forEach((item) => {
            updatePreview(item);
            updatePostLink(item);
            updateCaptionCount(item);

            if (updateItemState(item)) {
                readyCount += 1;
            }
        });

        const incompleteCount = items.length - readyCount;
        const isAtLimit = items.length >= maxPosts;

        if (countTarget) {
            countTarget.textContent = String(items.length);
        }

        if (summaryTarget) {
            if (incompleteCount === 0) {
                const postWord = readyCount === 1 ? 'post' : 'posts';
                const limitSuffix = isAtLimit ? ' (max reached).' : '.';
                summaryTarget.textContent = `${readyCount} ${postWord} ready to publish${limitSuffix}`;
            } else {
                summaryTarget.textContent = `${readyCount} ready, ${incompleteCount} incomplete. Incomplete posts will not be saved.`;
            }
        }

        if (incompleteWarning) {
            incompleteWarning.hidden = incompleteCount === 0;
        }
    };

    const createItemFromTemplate = () => {
        const baseItem = template.content.firstElementChild;

        if (!baseItem) {
            return null;
        }

        return baseItem.cloneNode(true);
    };

    const reindexItems = () => {
        let items = Array.from(list.querySelectorAll('[data-instagram-item]'));

        if (items.length === 0) {
            const fallbackItem = createItemFromTemplate();

            if (fallbackItem) {
                list.appendChild(fallbackItem);
                bindItem(fallbackItem);
                items = [fallbackItem];
            }
        }

        if (!items.length) {
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

            const removeButton = item.querySelector('[data-instagram-remove]');
            const moveUpButton = item.querySelector('[data-instagram-move-up]');
            const moveDownButton = item.querySelector('[data-instagram-move-down]');
            const duplicateButton = item.querySelector('[data-instagram-duplicate]');
            const isOnlyItem = items.length <= 1;
            const isAtTop = index === 0;
            const isAtBottom = index === items.length - 1;
            const isAtLimit = items.length >= maxPosts;

            if (removeButton) {
                removeButton.disabled = isDisabled || isOnlyItem;
            }

            if (moveUpButton) {
                moveUpButton.disabled = isDisabled || isAtTop;
            }

            if (moveDownButton) {
                moveDownButton.disabled = isDisabled || isAtBottom;
            }

            if (duplicateButton) {
                duplicateButton.disabled = isDisabled || isAtLimit;
            }
        });

        if (addButton) {
            addButton.disabled = isDisabled || items.length >= maxPosts;
        }

        refreshSummary();
    };

    const addInstagramPost = (options = {}) => {
        const sourceItem = options.sourceItem instanceof HTMLElement ? options.sourceItem : null;
        const afterItem = options.afterItem instanceof HTMLElement ? options.afterItem : null;
        const itemCount = list.querySelectorAll('[data-instagram-item]').length;

        if (isDisabled || itemCount >= maxPosts) {
            return null;
        }

        const newItem = sourceItem ? sourceItem.cloneNode(true) : createItemFromTemplate();

        if (!newItem) {
            return null;
        }

        if (afterItem && afterItem.parentElement === list) {
            list.insertBefore(newItem, afterItem.nextElementSibling);
        } else {
            list.appendChild(newItem);
        }

        bindItem(newItem);
        reindexItems();

        return newItem;
    };

    const bindItem = (item) => {
        if (!item || boundItems.has(item)) {
            return;
        }

        boundItems.add(item);

        const imageInput = item.querySelector('[data-instagram-image]');
        const postUrlInput = item.querySelector('[data-instagram-post-url]');
        const captionInput = item.querySelector('[data-instagram-caption]');
        const removeButton = item.querySelector('[data-instagram-remove]');
        const moveUpButton = item.querySelector('[data-instagram-move-up]');
        const moveDownButton = item.querySelector('[data-instagram-move-down]');
        const duplicateButton = item.querySelector('[data-instagram-duplicate]');
        const preview = item.querySelector('[data-instagram-preview]');
        const placeholder = item.querySelector('[data-instagram-placeholder]');
        const imageError = item.querySelector('[data-instagram-image-error]');

        const updateItem = () => {
            updatePreview(item);
            updatePostLink(item);
            updateCaptionCount(item);
            updateItemState(item);
            refreshSummary();
        };

        imageInput?.addEventListener('input', updateItem);
        postUrlInput?.addEventListener('input', updateItem);
        captionInput?.addEventListener('input', updateItem);

        imageInput?.addEventListener('blur', () => {
            imageInput.value = normalizeUrl(imageInput.value);
            updateItem();
        });

        postUrlInput?.addEventListener('blur', () => {
            postUrlInput.value = normalizeUrl(postUrlInput.value);
            updateItem();
        });

        removeButton?.addEventListener('click', () => {
            const hasAnyContent = getMissingFields(item).length < 3;

            if (hasAnyContent && !window.confirm('Remove this post?')) {
                return;
            }

            item.remove();
            reindexItems();
        });

        moveUpButton?.addEventListener('click', () => {
            const previousItem = item.previousElementSibling;

            if (!previousItem) {
                return;
            }

            list.insertBefore(item, previousItem);
            reindexItems();
            item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });

        moveDownButton?.addEventListener('click', () => {
            const nextItem = item.nextElementSibling;

            if (!nextItem) {
                return;
            }

            list.insertBefore(nextItem, item);
            reindexItems();
            item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });

        duplicateButton?.addEventListener('click', () => {
            const duplicatedItem = addInstagramPost({
                sourceItem: item,
                afterItem: item,
            });

            if (duplicatedItem) {
                duplicatedItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });

        if (preview && placeholder && imageError) {
            preview.addEventListener('load', () => {
                imageError.hidden = true;
                preview.hidden = false;
                placeholder.hidden = true;
            });

            preview.addEventListener('error', () => {
                preview.hidden = true;
                placeholder.hidden = true;
                imageError.hidden = false;
            });
        }

        updateItem();
    };

    Array.from(list.querySelectorAll('[data-instagram-item]')).forEach(bindItem);

    addButton?.addEventListener('click', addInstagramPost);

    handleInput?.addEventListener('blur', () => {
        handleInput.value = normalizeHandle(handleInput.value);
        updateProfileLink();
    });

    profileUrlInput?.addEventListener('input', updateProfileLink);
    profileUrlInput?.addEventListener('blur', () => {
        profileUrlInput.value = normalizeUrl(profileUrlInput.value);

        if (handleInput && handleInput.value.trim() === '') {
            handleInput.value = normalizeHandle(profileUrlInput.value);
        }

        updateProfileLink();
    });

    syncProfileButton?.addEventListener('click', () => {
        const normalizedHandle = normalizeHandle(handleInput?.value || '');

        if (handleInput) {
            handleInput.value = normalizedHandle;
        }

        if (!profileUrlInput) {
            return;
        }

        const profileUrl = buildProfileUrlFromHandle(normalizedHandle);
        if (profileUrl !== '') {
            profileUrlInput.value = profileUrl;
        }

        updateProfileLink();
    });

    updateProfileLink();
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
