import { BrowserRouter } from "react-router-dom";
import Routes from "./Routes";
import { AppBridgeProvider, QueryProvider, PolarisProvider, AppContext } from "./components";
import { Frame, Navigation } from "@shopify/polaris";
import { useEffect, useState } from "react";
import { NavigationMenu } from "@shopify/app-bridge-react";
import Pusher from "pusher-js";
import  '/assets/style.css'
export default function App() {
  // Any .tsx or .jsx files in /pages will become a route
  // See documentation for <Routes /> for more info
  const pages = import.meta.globEager("./pages/**/!(*.test.[jt]sx)*.([jt]sx)");
  const [shopDetails, setShopDetails] = useState("");
  const [shop, setShop] = useState("");

  // console.log("shopshopshopshop", shop);
  const [modernTrackingPageStyle, setModernTrackingPageStyle] = useState("");
  const [modernTrackingPageTitle, setModernTrackingPageTitle] = useState("");
    const [modernTrackingPageHandle, setModernTrackingPageHandle] = useState("");
    const [trackingPageType, setTrackingPageType] = useState("");

    const appUrl = window.location.origin;
    const apiUrl = `${appUrl}/api/`;
    // const appUrl = "https://phpstack-362288-4704544.cloudwaysapps.com/";
    // const apiUrl = `https://phpstack-362288-4704544.cloudwaysapps.com/api/`;
  // useEffect(() => {
  //   let location1 = location.search?.split("&shop=")[1];
  //   location1 = location1?.split("&timestamp=")[0];
  //   setShop(location1);
  //   setShopDetails(window.location.search);
  // }, []);

  useEffect(() => {
    const queryParams = new URLSearchParams(window.location.search);
    const shop = queryParams.get("shop");

    if (shop) {
      setShop(shop);
    }

    setShopDetails(window.location.search);
  }, []);

  // useEffect(() => {
  //   Pusher.logToConsole = true;
  //   const pusher = new Pusher("36be8a8cefc5559c7d79", {
  //     cluster: "ap2",
  //     encrypted: true,
  //   });
  //   const channel = pusher.subscribe(`my-channel-${shop}`);
  //   channel.bind("WebpushedEvent", (data) => {
  //     console.log("Received web based notification: ", data?.message);
  //   });
  //
  //   return () => {
  //     pusher.unsubscribe(`my-channel-${shop}`);
  //     pusher.disconnect();
  //   };
  // }, [shop]);

  return (
    <>
      <NavigationMenu
        navigationLinks={[
          {
            label: "Orders",
            destination: `/orders`,
          },
          {
            label: "Tracking page",
            destination: `/tracking-page`,
          },
          {
              label: "Analytics",
              destination: `/analytics`,
          },
          {
              label: "Billing",
              destination: `/billing`,
          },
          {
            label: "Settings",
            destination: `/settings`,
          },

          // {
          //   label: "Notification",
          //   destination: `/notifications`,
          // },
          // {
          //   label: "Integration",
          //   destination: `/integration`,
          // },
        ]}
      />
      <AppContext.Provider
        value={{
          apiUrl,
          appUrl,
          shop,
          setModernTrackingPageStyle,
          setModernTrackingPageTitle,
          setModernTrackingPageHandle,
          modernTrackingPageStyle,
          modernTrackingPageTitle,
          modernTrackingPageHandle,
            trackingPageType,
            setTrackingPageType
        }}
      >
        <Frame>
          <Routes pages={pages} />
        </Frame>
      </AppContext.Provider>
    </>
  );
}
