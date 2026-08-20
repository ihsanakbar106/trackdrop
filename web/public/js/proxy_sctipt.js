
import { TrackballControls } from 'three/addons/controls/TrackballControls.js';
import { CSS2DRenderer } from 'three/addons/renderers/CSS2DRenderer.js';
let  shopdomain="elias-project.myshopify.com";
if (typeof Shopify !== 'undefined' && Shopify.shop) {
    shopdomain = Shopify.shop; // Use Shopify object if available
} else {
    // If Shopify object is not available, check URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const shopurl = urlParams.get('shop'); // Get 'shop' parameter from the URL
    if (shopurl) {
        shopdomain = shopurl; // Use the 'shop' parameter if available
    }
}
// Function to send the iframe height to the parent window
function sendHeightToParent() {
    var height = document.documentElement.scrollHeight;
    window.parent.postMessage({ type: 'resizeIframe', height: height }, '*');
}

// Function to initialize MutationObserver
function initMutationObserver() {
    var observer = new MutationObserver(function() {
        sendHeightToParent();
    });

    // Start observing the document for changes
    observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true
    });
}

// Call the function initially and set up the observer
sendHeightToParent();
initMutationObserver();
let apiUrl="https://phpstack-1329250-4863091.cloudwaysapps.com/api";
console.log("shopdomain",shopdomain)
const markerSvg = `<svg viewBox="-4 0 36 36">
    <path fill="currentColor" d="M14,0 C21.732,0 28,5.641 28,12.6 C28,23.963 14,36 14,36 C14,36 0,24.064 0,12.6 C0,5.641 6.268,0 14,0 Z"></path>
    <circle fill="#ffffff" cx="14" cy="14" r="7"></circle>
</svg>`;
let cruntzoom = 260;
let finalzoom = 150;
let step = 5;
let delay = 60; // Delay between each zoom step (milliseconds)
const storeLocations = [
    { name: 'Store A', lat: 40.7128, lng: -74.0060 },
// Add more stores as needed
];
let Globe,camera;
fetch('https://phpstack-1329250-4863091.cloudwaysapps.com/js/countries_json.geojson').then(res => res.json()).then(countries => {

    Globe = new ThreeGlobe()
        .globeImageUrl('https://phpstack-1329250-4863091.cloudwaysapps.com/images/globe_background.png')
        .atmosphereColor('rgba(255,255,255,0.7)')
        .atmosphereAltitude(0.5) // Increase atmosphere range
        .hexPolygonsData(countries.features)
        .hexPolygonResolution(3)
        .hexPolygonMargin(0.3)
        .hexPolygonUseDots(false)
        .hexPolygonColor(() => '#4eafa5')
        .htmlElementsData(storeLocations)
        .htmlElement(d => {
            const el = document.createElement('div');
            el.style.position = 'absolute';
            el.style.transform = 'translate(-50%, -100%)'; // Adjust the position to match the exact lat/lng location
            el.style.pointerEvents = 'none'; // Avoid interfering with globe interactions

// Create marker element
            const marker = document.createElement('div');
            marker.innerHTML = markerSvg;
            marker.style.color = 'black';
            marker.style.width = '20px';
            marker.style.height = '20px';
            el.appendChild(marker);

// Create store name element
            const nameEl = document.createElement('div');
            nameEl.textContent = d.name;
            nameEl.style.fontSize = '12px';
            nameEl.style.textAlign = 'center';
            nameEl.style.marginTop = '5px';
            nameEl.style.background = '#ffffff';
            nameEl.style.padding = '5px';
            nameEl.style.borderRadius = '5px';
            nameEl.style.width = '100%';
// nameEl.style.transform = 'translateY(-10px)'; // Position the name above the marker

            el.appendChild(nameEl);

            return el;
        });
// .hexPolygonGeoJsonGeometry("geometry")
// .hexPolygonColor(() => `#${Math.round(Math.random() * Math.pow(2, 24)).toString(16).padStart(6, '0')}`);

// Setup renderer
    const renderers = [new THREE.WebGLRenderer({
        antialias: true,
        alpha: true
    }), new CSS2DRenderer()];
    renderers.forEach((r, idx) => {
        r.setSize(window.innerWidth, window.innerHeight);
// r.setPixelRatio(window.devicePixelRation)
        if (idx > 0) {
// overlay additional on top of main renderer
            r.domElement.style.position = 'absolute';
            r.domElement.style.top = '0px';
            r.domElement.style.pointerEvents = 'none';
        }
        document.getElementById('globeViz').appendChild(r.domElement);
    });
    /*const renderer = new THREE.WebGLRenderer({
    antialias: true,
    alpha: true
    });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(window.devicePixelRation)
    document.getElementById('globeViz').appendChild(renderer.domElement);*/

// Setup scene
    const scene = new THREE.Scene();
// Change background color here
    scene.background = new THREE.Color(0xD6DFE7); // Black background
    scene.add(Globe);
    scene.add(new THREE.AmbientLight(0xd7d9f1,  4));
    const directionalLight = new THREE.DirectionalLight(0xeeeeee, 1.5); // Increased intensity
    directionalLight.position.set(-10, 100, 50).normalize(); // Position the light
    scene.add(directionalLight);

// Setup camera
    camera = new THREE.PerspectiveCamera();
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    camera.position.z = cruntzoom;


// Add camera controls
    const tbControls = new TrackballControls(camera, renderers[0].domElement);
    tbControls.minDistance = 101;
    tbControls.maxDistance = 300;
    tbControls.minPolarAngle = 0; // Limit vertical rotation
    tbControls.maxPolarAngle = 180; // Limit vertical rotation
    tbControls.rotateSpeed = 1;
    tbControls.zoomSpeed = 0.1;

// // Create a map of store locations to globe points
// const storePoints = storeLocations.map(store => {
//     const position = Globe.getCoords(store.lat, store.lng);
//     return {
//         ...store,
//         position: new THREE.Vector3(position.x, position.y, position.z)
//     };
// });
// Update pov when camera moves
    Globe.setPointOfView(camera.position, Globe.position);
    tbControls.addEventListener('change', () => Globe.setPointOfView(camera.position, Globe.position));

// Kick-off renderer
    (function animate() { // IIFE
// Frame cycle
        tbControls.update();

// Add rotation to the Globe
        Globe.rotation.y += 0.0005; // Adjust this value to change rotation speed
        renderers.forEach(r => r.render(scene, camera));
// renderer.render(scene, camera);
        requestAnimationFrame(animate);
    })();
});


document.querySelectorAll('.trackify_order_nav_item').forEach(item => {
    item.addEventListener('click', function () {
// Remove active class and reset styles from all tabs
        document.querySelectorAll('.trackify_order_nav_item').forEach(tab => {
            tab.classList.remove('active');
            tab.style.color = '';
            tab.style.background = '';
            tab.querySelector('svg path').style.fill = '#0B1019'; // Reset SVG fill color
        });

// Add active class and styles to the clicked tab
        this.classList.add('active');
        this.style.color = 'rgb(255, 255, 255)';
        this.style.background = '#313131';
        this.querySelector('svg path').style.fill = '#fff'; // Change SVG fill color to white

// Hide all content containers
        document.querySelectorAll('.trackify_order_detail_container, .trackify_product_recommendations_wrapper').forEach(content => {
            content.style.display = 'none';
        });

// Show the content container corresponding to the clicked tab
        const targetId = this.getAttribute('data-target');
        document.getElementById(targetId).style.display = 'flex';
    });
});

document.querySelector('.trackify_close_btn').addEventListener('click', function () {
// Simulate a click on the "Track" tab
    document.querySelector('.trackify_order_nav_item[data-target="orderDetail"]').click();
});


// document.addEventListener('DOMContentLoaded', function () {
const tabs = document.querySelectorAll('.trackify_tab_bar');
const fields = document.querySelectorAll('.trackify_input_content');
const trackButton = document.querySelector('.trackify_form_button');
const apiResError = document.getElementById('apiResError');
const reloadBtnSvg = document.querySelector('#reloadBtnSvg');
const track_modern_loading = document.querySelector('.track_modern_loading');
const orderWrapper = document.getElementById('orderWrapper');
const formWrapper = document.querySelector('.trackify_form_wrapper');

const mapIframe = document.querySelector('.trackify_map_iframe');
const globeWrapper = document.querySelector('.trackify_globe_wrapper');
document.querySelector('#cancelBtnTrack').addEventListener('click', function () {
// Simulate a click on the "Track" tab
    formWrapper.style.display = "flex";

    orderWrapper.style.display = 'none';
    mapIframe.style.zIndex = '0';
    globeWrapper.classList.remove('trackify_globe_hidden_wrapper');
// location.reload();
});
document.querySelector('#reloadBtnTrack').addEventListener('click', function () {
// Simulate a click on the "Track" tab
    orderWrapper.style.display = 'none';
    mapIframe.style.zIndex = '0';
    globeWrapper.classList.remove('trackify_globe_hidden_wrapper');
    reloadBtnSvg.classList.add('rotate');
    trackButton.click()
});
// Tab click event listener
tabs.forEach(tab => {
    tab.addEventListener('click', function () {
        apiResError.style.display = 'none';
// Remove active class from all tabs
        tabs.forEach(t => t.classList.remove('trackify_tab_active_bar'));

// Add active class to the clicked tab
        this.classList.add('trackify_tab_active_bar');

// Hide all fields
        fields.forEach(field => field.style.display = 'none');

// Show fields related to the active tab
        const activeTab = this.getAttribute('data-tab');
        if (activeTab === 'order-number') {
            document.querySelector('[data-field="order-number"]').style.display = 'block';
            document.querySelector('[data-field="email-phone"]').style.display = 'block';
        } else if (activeTab === 'tracking-number') {
            document.querySelector('[data-field="tracking-number"]').style.display = 'block';
        }
    });
});

// Track button click event listener
trackButton.addEventListener('click', function (event) {
    event.preventDefault(); // Prevent form submission for demo purposes
    apiResError.style.display = 'none';
    apiResError.innerHTML="";
    let allValid = true; // Flag to check overall form validity

    fields.forEach(field => {
        const input = field.querySelector('.trackify_form_field');
        const errorMessage = field.querySelector('.trackify_form_error');

        if (field.style.display !== 'none') { // Validate only visible fields
            if (!input.value.trim()) {
                errorMessage.style.display = 'block'; // Show error message
                allValid = false;
            } else {
                errorMessage.style.display = 'none'; // Hide error message
            }
        }
    });

    if (allValid) {
// Form is valid, proceed with tracking logic
        console.log('Tracking...');

// Add loading state to the button
        trackButton.disabled = true;
        trackButton.innerHTML = 'Tracking...';

// Determine active tab and set track_type
        const activeTabElement = document.querySelector('.trackify_tab_active_bar');
        const trackType = activeTabElement.getAttribute('data-tab');
        track_modern_loading.style.display = 'flex';
        formWrapper.style.display = "none";
// Prepare the API URL based on active tab (track_type)

        let url;
        if (trackType === 'order-number') {
            const orderNumber = document.querySelector('[data-field="order-number"] .trackify_form_field[data-type="order-number"]');
            const orderEmail = document.querySelector('[data-field="email-phone"] .trackify_form_field[data-type="email-phone"]');
            url = `${ apiUrl }/search-tracking-number?shop=${shopdomain}&track_type=${encodeURIComponent(trackType)}&email=${encodeURIComponent(orderEmail.value.trim())}&order_number=${encodeURIComponent(orderNumber.value.trim())}`;
        } else if (trackType === 'tracking-number') {
            const trackingNumber = document.querySelector('[data-field="tracking-number"] .trackify_form_field[data-type="tracking-number"]');
            url = `${ apiUrl }/search-tracking-number?shop=${shopdomain}&track_type=${encodeURIComponent(trackType)}&tracking_number=${encodeURIComponent(trackingNumber.value.trim())}`;
        }

// Make the API call
        fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log('Order form is valid. API response:', data);
                if(data.status=="error"){
                    var errorMsg=data.message;
                    apiResError.style.display = 'block';
                    apiResError.innerHTML=errorMsg;
                    formWrapper.style.display = "flex";
                    return;
                }
                let Recommendation=data.recomendation;
                let Fullfilment=data.fulfillments[0];
                let status_name= document.querySelector('.status_name');
                let productRecommendationTab= document.querySelector('#productRecommendationTab');
                let productRecommendationsDetail= document.querySelector('#productRecommendationsDetail');
                let order_name=document.querySelector('.order_name');
                let produc_num=document.querySelector('.produc_num');
                let order_items_list_content=document.querySelector('.order_items_list_content');
                let trackify_order_tracking_details_content=document.querySelector('.trackify_order_tracking_details_content');
                let shipmentTitle=document.querySelector('.shipmentTitle');
                let shipmentLogo=document.querySelector('.shipmentLogo');
                let shipmentNumber=document.querySelector('.shipmentNumber');
                let line_items=JSON.parse(Fullfilment.line_items);
                let oder_line_items=Fullfilment.order.lineitems;
                let track_info=JSON.parse(Fullfilment.track_info);
                const details = track_info?track_info[0]?.Details:"";
                productRecommendationsDetail.innerHTML='';

                if (details) {
                    const encodedDetails = encodeURIComponent(details);
// setTimeout(() => {
                    mapIframe.src = `https://www.google.com/maps/embed/v1/place?key=AIzaSyDf5VCcikU0XbIhQtJ2mOpz6JHHND2Yggk&q=${encodedDetails}`;
// }, 5); // Small delay before setting the src
                } else {
                }
// Use a for loop with setTimeout to simulate gradual zoom
                for (let i = 0; i <= Math.abs(cruntzoom - finalzoom) / step; i++) {
                    setTimeout(() => {
                        if (cruntzoom > finalzoom) {
                            cruntzoom -= step;  // Decrease the zoom
                        } else if (cruntzoom < finalzoom) {
                            cruntzoom += step;  // Increase the zoom
                        }

// Update the camera's position
                        camera.position.z = cruntzoom;
                        if(cruntzoom==finalzoom) {
                            if (details) {
                                globeWrapper.classList.add('trackify_globe_hidden_wrapper');
                                mapIframe.style.zIndex = '3';
                            }
                            cruntzoom=260
                            camera.position.z = 260;
                        }
                    }, i * delay);  // Delay each step by `i * delay` milliseconds
                }
                orderWrapper.style.display = "block";

                status_name.innerHTML=track_info[0]?.checkpoint_status;
                order_name.innerHTML=Fullfilment.tracking_number;
                shipmentNumber.innerHTML=Fullfilment.tracking_company;
                shipmentTitle.innerHTML=Fullfilment.tracking_number;
                shipmentLogo.src = Fullfilment.carrier_name_base?.picture || Fullfilment.carrier_code_base?.picture || '';
                if(Recommendation.length>0){
                    console.log('Recommendation',Recommendation);
                    productRecommendationTab.style.display = 'flex';
                    let recomendedproducts="";
                    Recommendation.forEach((r_product)=>{
                        recomendedproducts+=`<div class="trackify_product_recommendations_item_content">
    <div class="trackify_product_recommendations_img_box" style="background-image: url(${r_product.image}); display: block">
    </div>
    <div class="content">
        <div class="trackify_product_title">${r_product.title}</div>

        <div class="trackify_product_price_container">
            <div class="trackify_product_price">
                ${r_product?.price ? r_product?.price  : ''}

            </div>
        </div>
    </div>
</div>`;
                    })
                    productRecommendationsDetail.innerHTML=recomendedproducts;
                }else{
                    productRecommendationTab.style.display = 'none';
                }
                console.log('track_info',track_info);

                let count = 0;
                let product_images="";
                let tracking_details_content="";
                line_items.forEach((item) => {
                    oder_line_items.forEach((odritem) => {
                        if(item.product_id==odritem.product_id){
                            product_images+='<div class="product_image_content">' +
                                '<img src="'+odritem.image+'">' +
                                '</div>';
                        }
                    });
                    console.log("Title:", item.title);
                    count++;
                });
                order_items_list_content.innerHTML=product_images;
                produc_num.innerHTML=count + "item(s)";


// Function to group the tracking info by date
                const groupByDate = (data) => {
                    return data.reduce((acc, item) => {
                        const date = new Date(item.Date).toLocaleDateString('en-US', { day: '2-digit', month: 'short' });
                        if (!acc[date]) {
                            acc[date] = [];
                        }
                        acc[date].push(item);
                        return acc;
                    }, {});
                };

// Function to extract time
                const getTime = (dateStr) => {
                    const date = new Date(dateStr);
                    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
                };
                const groupedData = groupByDate(track_info);

                /*track_info.forEach((trackInfo,index) => {
                // Adjust the following code according to the actual properties of trackInfo
                const dateObj = new Date(trackInfo.Date); // Create a Date object from the string
                const date = dateObj.getDate(); // Get the day of the month (1-31)
                const month = dateObj.toLocaleString('default', { month: 'short' }); // Get the abbreviated month name (e.g., 'Mar')
                const time = dateObj.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }); // Get time in 'hh:mm am/pm' format

                const location = trackInfo.Details;
                const detail = trackInfo.StatusDescription; // Example, replace with actual trackInfo.detail
                const firstDetailClass = index === 0 ? 'trackify_event_first_detail' : ''; // Add class only to the first record

                tracking_details_content += `
                <div class="trackify_order_desc_wrapper">
                    <div class="trackify_order_desc_left_content">
                        <div class="trackify_track_date_icon_box" style="color: rgb(255, 255, 255); background: #616161;">
                            <div class="trackify_track_date_text">${date}</div>
                            <div class="trackify_track_date_text">${month}</div>
                        </div>
                        <div class="trackify_date_line"></div>
                    </div>
                    <div class="trackify_order_desc_container">
                        <div class="trackify_order_desc_right_content">
                            <div class="trackify_event_time">
                                <div>${time}</div>
                            </div>
                            <div class="trackify_event_detail ${firstDetailClass}">
                                ${location}, ${detail}
                            </div>
                        </div>
                    </div>
                </div>
                `;
                });*/
                Object.keys(groupedData).forEach((date, dateIndex) => {
                    tracking_details_content += `
<div class="trackify_order_desc_wrapper">
    <div class="trackify_order_desc_left_content">
        <div class="trackify_track_date_icon_box" style="color: rgb(255, 255, 255); background: #616161;">
            <div class="trackify_track_date_text">${date.split(' ')[0]}</div>
            <div class="trackify_track_date_text">${date.split(' ')[1]}</div>
        </div>
        <div class="trackify_date_line"></div>
    </div>
    `;

                    groupedData[date].forEach((trackInfo, index) => {
                        const time = getTime(trackInfo.Date);
                        const detailClass = index === 0 && dateIndex === 0 ? 'trackify_event_first_detail' : ''; // Apply class to the first item on the first date only

                        tracking_details_content += `
    <div class="trackify_order_desc_container">
        <div class="trackify_order_desc_right_content">
            <div class="trackify_event_time">
                <div>${time}</div>
            </div>
            <div class="trackify_event_detail ${detailClass}">
                ${trackInfo.Details ? trackInfo.Details + ', ' : ''} ${trackInfo.StatusDescription}
            </div>
        </div>
    </div>
    `;
                    });

                    tracking_details_content += '</div>'; // Closing the wrapper div for each date
                });
                trackify_order_tracking_details_content.innerHTML=tracking_details_content;
// Handle the API response here
            })
            .catch(error => {
                console.error('API call error:', error);
            })
            .finally(() => {
// Reset loading state after API call is complete
                trackButton.disabled = false;
                trackButton.innerHTML = 'Track';
                reloadBtnSvg.classList.remove('rotate');
                track_modern_loading.style.display = 'none';

            });;

        console.log('Order form is valid.');
    }
});

// Input change event listener to remove error message
fields.forEach(field => {
    const input = field.querySelector('.trackify_form_field');
    const errorMessage = field.querySelector('.trackify_form_error');

    input.addEventListener('input', function () {
        if (input.value.trim()) {
            errorMessage.style.display = 'none'; // Hide error message on input change
        }
    });
});
