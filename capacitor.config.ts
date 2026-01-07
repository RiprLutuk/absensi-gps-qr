import { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.absensi.test',
  appName: 'Absensi',
  webDir: 'public',
  server: {
    url: 'https://test-absesnsi.pandanteknik.com',
    androidScheme: 'https',
    cleartext: true,
    allowNavigation: ['*']
  },
  android: {
    allowMixedContent: true,
    backgroundColor: '#ffffff',
    captureInput: true,
    loggingBehavior: 'none',
    webContentsDebuggingEnabled: false
  },
  plugins: {
    Camera: {
      permissions: ['camera', 'photos']
    },
    Geolocation: {
      permissions: ['location']
    },
    Filesystem: {
      androidPermissions: [
        'android.permission.READ_EXTERNAL_STORAGE',
        'android.permission.WRITE_EXTERNAL_STORAGE'
      ]
    },
    SplashScreen: {
      launchShowDuration: 2000,
      launchAutoHide: true,
      backgroundColor: "#ffffff",
      androidSplashResourceName: "splash",
      showSpinner: false,
      androidScaleType: "CENTER_INSIDE",
      splashFullScreen: true,
      splashImmersive: true
    }
  }
};

export default config;
