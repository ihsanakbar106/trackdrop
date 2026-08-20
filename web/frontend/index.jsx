import {createRoot} from "react-dom/client";
import {Route, Routes, BrowserRouter, useNavigate} from "react-router-dom";
import "./index.css";
import "./custom.css";
import App from "./App";
import {
    AppBridgeProvider,
    QueryProvider,
    PolarisProvider,
    AppContext,
} from "./components";

const root = createRoot(document.getElementById("app"));
root.render(
    <PolarisProvider>
        <BrowserRouter>
            <AppBridgeProvider>
                <QueryProvider>
                    <App/>
                </QueryProvider>
            </AppBridgeProvider>
        </BrowserRouter>
    </PolarisProvider>
);
