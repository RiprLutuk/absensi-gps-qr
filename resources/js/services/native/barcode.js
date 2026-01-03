import { BarcodeScanner } from "@capacitor-community/barcode-scanner";

export async function startNativeBarcodeScanner(onScanSuccess) {
    const perm = await BarcodeScanner.checkPermission({ force: true });

    if (!perm.granted) {
        alert("Camera permission is required");
        return;
    }

    BarcodeScanner.hideBackground();

    const result = await BarcodeScanner.startScan();

    BarcodeScanner.showBackground();
    BarcodeScanner.stopScan();

    if (result?.hasContent) {
        await onScanSuccess(result.content);
    }
}
