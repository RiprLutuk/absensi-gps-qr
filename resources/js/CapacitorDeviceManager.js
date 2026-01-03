import { Geolocation } from "@capacitor/geolocation";

export default class CapacitorDeviceManager {
    async getCurrentLocation() {
        const perm = await Geolocation.requestPermissions();

        if (perm.location !== "granted") {
            throw new Error("Location permission denied");
        }

        const pos = await Geolocation.getCurrentPosition({
            enableHighAccuracy: true,
            timeout: 10000,
        });

        return {
            latitude: pos.coords.latitude,
            longitude: pos.coords.longitude,
        };
    }
}
