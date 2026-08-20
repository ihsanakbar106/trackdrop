<style>
    #track-drop{
        width: 100%;
        border: 0px;
        height:100vh;
    }

    footer,.footer,.shopify-section-group-footer-group,body,.drawer  {
        display: none !important;
    }
    ::-webkit-scrollbar {
        display: none;
    }
</style>

<iframe id="track-drop" src="" frameborder="0" scrolling="no" allow="geolocation"
        sandbox="allow-scripts allow-same-origin allow-popups allow-forms allow-top-navigation"
></iframe>
<script>
    // Function to update the src of the iframe
    var trackingNumber="{{$tracking_number}}";
    function updateIframeSrc(shopName) {
        // Clear the body content
        document.body.innerHTML = '';
        var style = document.createElement('style');

        // Set the inner HTML to your CSS
        style.innerHTML = `

        #track-drop {
            width: 100%;
            border: 0px;
            height: 100vh;
        }
        .drawer {
            display: none;
        }
        footer, .footer, .shopify-section-group-footer-group {
            display: none !important;
        }
        ::-webkit-scrollbar {
            display: none;
        }
    `;

        // Append the <style> element to the <body>
        document.body.appendChild(style);
        // Create the iframe element

        var iframeSrc = `https://app.theautotrack.com/track?shop=${shopName}`;

        // Check if trackingNumber has a value and append it to the iframe src
        if (trackingNumber) {
            iframeSrc += `&tracking_number=${trackingNumber}`;
        }
        var iframe = document.createElement('iframe');
        iframe.id = 'track-drop';
        iframe.frameBorder = '0';
        iframe.scrolling = 'no';
        iframe.allow = 'geolocation';
        iframe.style.width = '100%';
        iframe.style.height = '100vh';
        iframe.sandbox = 'allow-scripts allow-same-origin allow-popups allow-forms allow-top-navigation';

        // Set the src attribute of the iframe
        iframe.src = iframeSrc;

        // Append the iframe to the body
        document.body.appendChild(iframe);

        // Handle iframe load success
        iframe.onload = function () {
            iframe.style.display = 'block';
        };

        // Handle iframe load error (if it refuses to connect)
        iframe.onerror = function () {
            document.body.innerHTML = 'Failed to load content. Please refresh the page.'; // Show error message
        };


    }

    // Function to adjust the iframe height
    function adjustIframeHeight(event) {
        if (event.data.type === 'resizeIframe' && (event.origin === 'https://phpstack-1329250-4863091.cloudwaysapps.com'|| event.origin === 'https://app.theautotrack.com')) {
            var iframe = document.getElementById('track-drop');
            iframe.style.height = event.data.height + 'px';
        }
    }

    // Set up the event listener
    window.addEventListener('message', adjustIframeHeight, false);

    // Initialize the iframe with the shop name (replace with actual Shopify shop object)
    // document.body.innerHTML = '';

    updateIframeSrc(Shopify.shop || 'example-shop');
</script>
