var mage = (() => {
    var loaded = false;
    var localStorage = window.localStorage;
    var doc = document;
    window.addEventListener('load', () => {
        loaded = true;
    });
    
    function isLoaded(){
        return loaded;
    }
    
    function getCookie(name) {
            let match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? decodeURIComponent(match[2]) : null;
        }
    
    function setCookie(name, value, days = 7, path = "/") {
        let expires = "";
        if (days) {
            let date = new Date();
            date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = `${encodeURIComponent(name)}=${encodeURIComponent(value)}${expires}; path=${path}`;
    }
    
    function displayMessages() {
        const cookieMessages = getCookie('mage-messages'); // Ensure the cookie name matches Magento's setup
        if (!cookieMessages) return;
    
        try {
            let messages = JSON.parse(cookieMessages);
            if (!Array.isArray(messages) || messages.length === 0) return;
            const messageContainer = document.querySelector('.page.messages');
    
            messageContainer.innerHTML = ""; // Clear existing messages
    
            messages.forEach(message => {
                let messageDiv = document.createElement("div");
                messageContainer.style.display = "block";
                messageDiv.className = `message-${message.type} ${message.type} message`;
                messageDiv.setAttribute("data-ui-id", `message-${message.type}`);
                messageDiv.innerHTML = `<div>${message.text}</div>`;
    
                messageContainer.appendChild(messageDiv);
            });
            document.cookie = "mage-messages=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                // Optionally, clear messages after 5 seconds
                setTimeout(() => {
                    const messageContainer = document.querySelector('.page.messages');
                    const messageDiv = document.querySelector("message");
                    messageContainer.innerHTML = "";
                    messageContainer.style.display = "none";
            }, 10000);
        } catch (error) {
            console.error("Error parsing messages from cookie:", error);
        }
    }
    
    const defaultSectionData = [
        "messages", "customer",
        "compare-products",
        "last-ordered-items",
        "cart", "wishlist",
        "loggedAsCustomer"
    ];
    
    async function loadSectionData(sections=defaultSectionData) {
        try {
            let valid = false;
            let data = null;
            const privateContentVersionCokies = getCookie('private_content_version');
            const privateContentVersionLocal = localStorage.getItem('private_content_version');
            const url = BASE_URL+`customer/section/load/?sections=${encodeURIComponent(sections.join(','))}`;
    
            if(privateContentVersionCokies && privateContentVersionLocal && 
               privateContentVersionCokies === privateContentVersionLocal) {
                const ttl = new Date(localStorage.getItem('mage-cache-timeout'));
                if (ttl < new Date()) {
                    valid = false
                    localStorage.removeItem('mage-cache-storage');
                    localStorage.removeItem('mage-cache-timeout');
                    localStorage.removeItem('private_content_version');
                } else {
                    valid = true
                    data = JSON.parse(localStorage.getItem('mage-cache-storage'));
                }
            }
            if (!valid){
                const request = new Request(url,
                    {
                        method: 'GET',
                        cache: 'no-store',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                ); 
                const response = await fetch(request);
    
                if (!response.ok) {
                    throw new Error(`Failed to fetch data: ${response.status} ${response.statusText}`);
                }
    
                data = await response.json();
                localStorage.setItem('mage-cache-storage', JSON.stringify(data));
                localStorage.setItem('mage-cache-timeout', new Date(Date.now() + (3600 * 1000)).toISOString());
                const version = getCookie('private_content_version');
                localStorage.setItem('private_content_version', version);
    
                setCookie(
                    'section_data_ids',
                    JSON.stringify(
                        Object.keys(data).reduce((ids, key) => {
                            ids[key] = data[key]['data_id'];
                            return ids;
                        }, {})
                    ),
                    false,
                    true
                );
            }
            if(typeof data === null){
                console.log("Private Data issue");
            }
    
            //console.log("Fetched private content:", data);
            updateCartData(data.cart);
            updateCustomer(data.customer);
            updateFormKeys();
            return data;
        } catch (error) {
            console.error("Error fetching private content:", error);
            return null;
        }
    }
    
    function getLocalStorage() {
        if (!window.localStorage) {
            console.warn('Local Storage is issue');
            return false;
        }
    return window.localStorage
    }
    
    function updateCustomer(data) {
        let isLoggedIn = false;
            if (data) {
                const name = data.firstname;
    
                const customerName = document.querySelector('.customer-name');
                if (customerName && name && name !== '') {
                    isLoggedIn = true;
                    customerName.innerHTML += '<span id="customer-account-link">Welcome, ' + name + '</span>';
                    const customerMenu = document.querySelector(".customer-menu");
                    document.querySelectorAll('.link.logout').forEach(elem => (elem.style.display="none"));
                    document.querySelectorAll('.link.login').forEach(elem => (elem.style.display="block"));
    
                    customerName.addEventListener("click", function (event) {
                        event.preventDefault();
                        if (customerMenu) {
                            customerMenu.style.display = customerMenu.style.display === "block" ? "none" : "block";
                        }
                    });
                }
            }
            if (!isLoggedIn){
                document.querySelectorAll('.link.logout').forEach(elem => (elem.style.display="block"));
                document.querySelectorAll('.link.login').forEach(elem => (elem.style.display="none"));
            }
    }
    
    function updateCartData(data) {
            if (data) {
                const summaryCount = data.summary_count; // Number of items
                if (summaryCount !== 0 && summaryCount !== null){
                const subtotalAmount = parseFloat(data.subtotalAmount); // Subtotal amount
                // Updatethe cart counter
                const subtotalElement = document.querySelector('.counter-number');
                if (subtotalElement) {
                    const cartElement = document.querySelector('.counter.qty.empty');
                        if (cartElement) {
                            cartElement.classList.remove('empty');
                        }
                    subtotalElement.innerText = subtotalAmount ? `$${subtotalAmount.toFixed(2)}` : '';
                }
            }
        }
    }
    
    getFormKey = function () {
        let formKey = getCookie('form_key');
        if (!formKey) {
            formKey = crypto.randomUUID(); // More secure than random strings
            setCookie('form_key', formKey, 86400); // Set for 1 day
        }
        return formKey;
    };
    
    updateFormKeys = function () {
        const formKey = getFormKey().replace(/-/g, '\\u002D');
        document.querySelectorAll('input[name="form_key"]').forEach(input => input.value = formKey);
    };
    
    onLoad = (callback, ...args) => {
        window.addEventListener("load", () => callback(...args));
    };
    
    onDOM = (callback, ...args) => {
        document.addEventListener("DOMContentLoaded", () => callback(...args));
    }
    
    const checkInterval = 150;
    const loadTimeout = 3000
    
    // Function to run code only after the page has loaded, with optional arguments
    runAfterLoad = (interval = checkInterval, callback, ...args) => {
        let counter = 5;
        const checkLoad = setInterval(() => {
            if (isLoaded()) {
                clearInterval(checkLoad); 
                callback(...args);
            }
            counter++;
        }, interval);
    }
    
    loadScript = (src) => {
        return new Promise((resolve, reject) => {
            const script = document.createElement("script");
            script.src = src;
            script.async = true;
            script.onload = () => resolve(src);
            script.onerror = () => reject(new Error(`Failed to load script: ${src}`));
            document.head.appendChild(script);
        });
    }
    
    getUenc = () => {
        return window.currentUenc;
    }
    
    async function addToCompare(productId) {
        try {
            const formKey = getFormKey();
            const postUrl = BASE_URL + `catalog/product_compare/add/`;
            const bodyData = new URLSearchParams({
                form_key: formKey,
                product: productId,
                uenc: getUenc()
            });
    
            const request = new Request(postUrl,
                {
                    method: 'POST',
                    cache: 'no-store',
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
                    },
                    body: bodyData,
                    mode: "cors",
                    credentials: "include"
                }
            ); 
    
            const response = await fetch(request);
    
            if (response.redirected) {
                window.location.href = response.url;
            }
            loadSectionData();
        } catch (error) {
            if (typeof window.dispatchMessages !== "undefined") {
                alert(`❌ Error: ${error.message || "An unexpected error occurred."}`);
            }
        }
    }
    
    async function addToWishlist(productId) {
        try {
            const formKey = getFormKey();
            const postUrl = BASE_URL + `wishlist/index/add/`;
            const uenc = getUenc();
    
            const response = await fetch(postUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
                },
                body: new URLSearchParams({
                    form_key: formKey,
                    product: productId,
                    uenc: uenc,
                }),
                mode: "cors",
                credentials: "include",
            });
    
            if (response.redirected) {
                window.location.href = response.url;
                return;
            }
    
            if (!response.ok) {
                alert("⚠️ Could not add item to wishlist.");
                return;
            }
    
            const data = await response.json();
    
            alert(
                data.success
                    ? "✅ Product has been added to your Wish List."
                    : `❌ ${data.error_message || "Something went wrong!"}`
            );
    
            // Trigger customer section reload
            loadSectionData()
        } catch (error) {
            alert(`❌ Error: ${error.message || "An unexpected error occurred."}`);
        }
    }
    
    return {
            displayMessages,
            loadSectionData,
            getLocalStorage,
            updateCartData,
            updateFormKeys,
            addToWishlist,
            localStorage,
            addToCompare,
            getFormKey,
            isLoaded,
            getUenc,
            onLoad,
            onDOM,
            doc
        };
    })();

    mage.loadSectionData();
    
    mage.onDOM( function () {
        const navToggle = document.querySelector(".action.nav-toggle");
        const navMenu = document.querySelector(".nav-sections");
        // Toggle menu on click
        navToggle.addEventListener("click", function () {
            navMenu.classList.toggle("active");
        });
        // Close menu when clicking outside
        document.addEventListener("click", function (event) {
            if (!navMenu.contains(event.target) && event.target !== navToggle) {
                navMenu.classList.remove("active");
            }
        });
    });
    
    mage.onDOM( function () {
        const filterSections = document.querySelectorAll(".filter-options-item");
        filterSections.forEach(section => {
            const title = section.querySelector(".filter-options-title");
            title.addEventListener("click", function () {
                // Toggle active class
                section.classList.toggle("active");
            });
        });
    });
    
    mage.onDOM( function () {
        mage.displayMessages();
        setInterval(() => {
                    mage.displayMessages();;
                }, 5000);
    });
    
    mage.onDOM( function () {
        document.querySelectorAll(".block.related li.item.product.product-item").forEach(productItem => {
            productItem.style.display = "";
                });
        }
    );
    
    if(window.productType === "simple") {
        const button = document.getElementById("product-addtocart-button");
        if (button) {
            button.removeAttribute("disabled");
        }
    }
    
    if(window.controller === "category"){
    const buttons = document.querySelectorAll(".action.tocart.primary");
    if (buttons) {
            buttons.forEach(btn => {
                    btn.removeAttribute("disabled");
                });
    }}
    
    window.onload = () => {
        const sorter = document.getElementById("sorter");
        if(sorter){
            sorter.addEventListener("change", function () {
                const selectedValue = sorter.value;
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set("product_list_order", selectedValue);
                window.location.href = currentUrl.toString();
            });
        }
    };
    
    mage.onDOM( function () {
        const limiterSelect = document.getElementById("limiter");
    
        if (limiterSelect) {
            limiterSelect.addEventListener("change", function () {
                const selectedValue = this.value;
                const currentUrl = new URL(window.location.href);
    
                // Update the 'limit' query parameter
                currentUrl.searchParams.set("product_list_limit", selectedValue);
    
                // Redirect to the updated URL
                window.location.href = currentUrl.toString();
            });
        }
    });
    
    mage.onDOM( function () {
        const sortDirectionLink = document.querySelector("[data-role='direction-switcher']");
    
        if (sortDirectionLink) {
            sortDirectionLink.addEventListener("click", function (event) {
                event.preventDefault(); // Prevent the default link behavior
    
                const currentUrl = new URL(window.location.href);
                const currentDirection = currentUrl.searchParams.get("dir") || "asc"; // Default to ascending
                const newDirection = currentDirection === "asc" ? "desc" : "asc"; // Toggle between asc and desc
    
                // Update the 'dir' query parameter
                currentUrl.searchParams.set("product_list_dir", newDirection);
    
                // Redirect to the updated URL
                window.location.href = currentUrl.toString();
            });
        }
    });
    
    mage.onDOM(function () {
        function toggleSection(event) {
            event.preventDefault();
    
            const targetId = this.getAttribute("href");
            const contentElements = document.querySelectorAll('.section-item-content.nav-sections-item-content');
            contentElements.forEach(content => {
                content.style.display = "none";
            });
    
            document.querySelectorAll(".section-item-title").forEach(title => {
                title.classList.remove("active");
            });
    
            document.getElementById(targetId.substring(1)).style.display = 'block';
    
            this.parentElement.setAttribute("aria-hidden", "true");
            this.parentElement.classList.add("active");
    
        }
    
        // Attach event listeners to menu and account links
        document.querySelectorAll("a.nav-sections-item-switch").forEach(link => {
            link.addEventListener("click", toggleSection);
        });
        var n = 0;
        document.querySelectorAll(".section-item-content.nav-sections-item-content").forEach(menu => {
            if(menu.id !== "store.menu"){
                menu.style.display = "none";
            }
        });
    
        // Select all parent menu items
        document.querySelectorAll("level0.category-item.parent > a").forEach(menuItem => {
            menuItem.addEventListener("click", function (event) {
                event.preventDefault(); // Prevent the default link behavior
    
                let submenu = this.nextElementSibling; // Get the submenu
                if (submenu) {
                    submenu.style.display = submenu.style.display === "block" ? "none" : "block";
                }
            });
        });
    });
    
    mage.onDOM(() => {
        // Select the search button and the input field
        const searchButton = document.querySelector('[data-role="minisearch-label"]');
        const searchBlock = document.querySelector('form.minisearch .control');
    
        if (searchButton && searchBlock) {
            searchButton.addEventListener('click', function() {
                // Toggle the display property
                if (searchBlock.style.display === 'block') {
                    searchBlock.style.display = 'none';
                } else {
                    searchBlock.style.display = 'block';
                }
            });
        }
    
        const shopByTitle = document.querySelector('strong[data-role="title"]');
        const filterOptions = document.querySelector('.filter-options');
    
        if (shopByTitle && filterOptions) {
            shopByTitle.addEventListener("click", function () {
                filterOptions.style.display =
                    filterOptions.style.display === "none" || filterOptions.style.display === ""
                    ? "block"
                    : "none";
            });
        }
    });