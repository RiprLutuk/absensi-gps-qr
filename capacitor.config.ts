import { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.absensi.test',
  appName: 'Absensi Test',
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
    loggingBehavior: 'debug',
    webContentsDebuggingEnabled: true
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
  }
};

export default config;
