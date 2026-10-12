(function ($) {
    $(document).ready(function () {

        // Open mobile sidebar
        $(document).on("click", ".mobile-nav-icon", function (e) {
            e.preventDefault();
            $(".mobile-sidebar").addClass("mobile-menu-active");
        });

        // Close mobile sidebar
        $(document).on("click", ".menu-close", function (e) {
            e.preventDefault();
            $(".mobile-sidebar").removeClass("mobile-menu-active");
        });

        // Close sidebar when clicking outside
        $(document).on("click", function (e) {
            if (
                !$(e.target).closest(".mobile-sidebar, .mobile-nav-icon").length
            ) {
                $(".mobile-sidebar").removeClass("mobile-menu-active");
            }
        });

        // Mobile submenu initialization
        $(".mobile-nav").each(function () {
            const mobileMenu = $(this);

            // Mark parent items that have submenus
            mobileMenu.find("li").has("ul").addClass("has-sub");

            // Add submenu buttons only once
            mobileMenu.find("li.has-sub").each(function () {
                const item = $(this);

                if (item.children(".submenu-button").length === 0) {
                    item.prepend(
                        '<span class="submenu-button" aria-label="Toggle submenu">' +
                        '<em></em></span>'
                    );
                }
            });
        });

        // Toggle submenus using delegated events
        $(document).on("click", ".mobile-nav .submenu-button", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const button = $(this);
            const submenu = button.siblings("ul").first();

            if (!submenu.length) return;

            button.toggleClass("submenu-opened");

            submenu.stop(true, true).slideToggle(250);
        });

        // Prevent submenu links from triggering parent handlers
        $(document).on("click", ".mobile-nav a", function (e) {
            e.stopPropagation();

            const href = $(this).attr("href");

            // Close the sidebar only when navigating to a real page
            if (href && href !== "#") {
                $(".mobile-sidebar").removeClass("mobile-menu-active");
            }
        });

        // Reset submenu visibility on desktop
        $(window).on("resize", function () {
            if ($(window).width() > 991) {
                $(".mobile-nav ul").show();
                $(".mobile-nav ul.sub-menu").hide();
                $(".mobile-nav .submenu-button").removeClass("submenu-opened");
                $(".mobile-sidebar").removeClass("mobile-menu-active");
            }
        });
    });
})(jQuery);