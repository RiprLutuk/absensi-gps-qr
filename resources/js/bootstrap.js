import axios from "axios";
import CapacitorDeviceManager from "./CapacitorDeviceManager";
import { getCurrentLocation } from "./services/location.service";
import { startNativeBarcodeScanner } from "./services/native/barcode";

window.axios = axios;
window.startNativeBarcodeScanner = startNativeBarcodeScanner;
window.deviceManager = new CapacitorDeviceManager();
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
