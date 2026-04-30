<!-- Page Loader - Place this as the FIRST child inside <body> -->
<div id="page-loader" class="page-loader" role="status" aria-label="Loading page...">
    <div class="loader-content">
        <div class="tech-spinner">
            <div class="spinner-circle"></div>
            <div class="spinner-circle"></div>
            <div class="spinner-circle"></div>
        </div>
        <div class="loader-text">
            <span class="logo-text">Tema</span><span class="tech-text">Tech</span>
        </div>
        <p class="loading-message">Loading...</p>
    </div>
</div>

<style>
    .page-loader {
        position: fixed;
        inset: 0;
        /* modern way instead of top/left/width/height */
        background: #FFFFFF;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 1;
        transition: opacity 0.6s ease;
        pointer-events: none;
        /* allows interaction during fade-out */
    }

    .page-loader.hidden {
        opacity: 0;
    }

    /* Rest of your existing styles (tech-spinner, loader-text, etc.) remain the same */
    .loader-content {
        text-align: center;
        color: black;
    }

    .tech-spinner {
        position: relative;
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
    }

    .spinner-circle {
        position: absolute;
        width: 100%;
        height: 100%;
        border: 4px solid rgba(255, 107, 53, 0.2);
        border-top: 4px solid #FF6B35;
        border-radius: 50%;
        animation: spin 1.2s linear infinite;
    }

    .spinner-circle:nth-child(2) {
        border-top-color: #000000;
        animation-delay: 0.2s;
    }

    .spinner-circle:nth-child(3) {
        border-top-color: #000000;
        animation-delay: 0.4s;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .loader-text {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 8px;
        letter-spacing: 2px;
    }

    .logo-text {
        color: #FF6B35;
    }

    .tech-text {
        color: #000000;
    }

    .loading-message {
        color: #B8A394;
        font-size: 0.95rem;
        margin-top: 10px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 0.6;
        }

        50% {
            opacity: 1;
        }
    }

    /* Hide loader when page is fully loaded */
    .page-loaded .page-loader {
        opacity: 0;
        pointer-events: none;
    }
</style>

<script>
    (function() {
        const loader = document.getElementById('page-loader');
        if (!loader) return;

        let navigationTimeout;

        function hideLoader() {
            loader.classList.add('hidden');

            // Remove from DOM only after transition finishes (cleaner)
            loader.addEventListener('transitionend', function handler() {
                loader.remove();
                loader.removeEventListener('transitionend', handler);
            }, {
                once: true
            });
        }

        // Hide loader when DOM is ready (best for perceived speed)
        function initHide() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    setTimeout(hideLoader, 120); // small delay prevents flash on very fast pages
                });
            } else {
                setTimeout(hideLoader, 120);
            }

            // Fallback: also hide on full load
            window.addEventListener('load', () => {
                if (!loader.classList.contains('hidden')) hideLoader();
            }, {
                once: true
            });
        }

        initHide();

        // Show loader on internal navigation
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[href]');
            if (!link) return;

            const href = link.getAttribute('href') || link.href;

            // Skip: external links, new tabs, downloads, hash-only, current page
            const isExternal = !href.startsWith('/') && !href.includes(window.location.hostname);
            const opensNewTab = link.target === '_blank' || e.ctrlKey || e.metaKey || e.shiftKey;
            const isDownload = link.hasAttribute('download');
            const isSamePage = href === '#' || href === window.location.href || href.startsWith('#');

            if (isExternal || opensNewTab || isDownload || isSamePage) return;

            // Show loader
            loader.classList.remove('hidden');
            loader.style.opacity = '1';
            loader.setAttribute('aria-busy', 'true');

            // Safety: hide after 8 seconds if something goes wrong
            clearTimeout(navigationTimeout);
            navigationTimeout = setTimeout(() => {
                if (!loader.classList.contains('hidden')) hideLoader();
            }, 8000);
        });

        // Optional: Support back/forward browser buttons
        window.addEventListener('popstate', () => {
            loader.classList.remove('hidden');
            loader.style.opacity = '1';
        });
    })();
</script>