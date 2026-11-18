const { app, BrowserWindow, Menu, shell } = require('electron');
const { spawn } = require('child_process');
const fs = require('fs');
const net = require('net');
const path = require('path');

const rootFile = path.join(process.resourcesPath || '', 'app.asar.unpacked', 'photobooth-root.txt');
const root = process.env.PHOTOBOOTH_ROOT
    || (app.isPackaged && fs.existsSync(rootFile) ? fs.readFileSync(rootFile, 'utf8').trim() : path.resolve(__dirname, '..'));
const scriptDir = fs.existsSync(path.join(root, 'scripts', 'start-server.vbs'))
    ? path.join(root, 'scripts')
    : path.join(process.resourcesPath || '', 'app.asar.unpacked', 'scripts');
const serverUrl = 'http://127.0.0.1:8000/admin';

app.setAppUserModelId('com.photobooth.app');

function startServices() {
    const script = path.join(scriptDir, 'start-server.vbs');
    if (!fs.existsSync(script)) return;

    const child = spawn('wscript.exe', [script, root], {
        detached: true,
        stdio: 'ignore',
        windowsHide: true,
    });
    child.unref();
}

function waitForPort(port, tries = 30) {
    return new Promise((resolve) => {
        let attempt = 0;

        const next = () => {
            attempt += 1;
            if (attempt >= tries) {
                resolve(false);
                return;
            }
            setTimeout(check, 1000);
        };

        const check = () => {
            const socket = net.connect(port, '127.0.0.1');
            socket.setTimeout(500);
            socket.once('connect', () => {
                socket.destroy();
                resolve(true);
            });
            socket.once('error', () => {
                socket.destroy();
                next();
            });
            socket.once('timeout', () => {
                socket.destroy();
                next();
            });
        };

        check();
    });
}

function buildMenu() {
    const template = [
        {
            label: 'Edit',
            submenu: [
                { role: 'undo' },
                { role: 'redo' },
                { type: 'separator' },
                { role: 'cut' },
                { role: 'copy' },
                { role: 'paste' },
                { role: 'selectAll' },
            ],
        },
        {
            label: 'Tampilan',
            submenu: [
                { role: 'reload' },
                { role: 'forceReload' },
                { type: 'separator' },
                { role: 'togglefullscreen' },
            ],
        },
        {
            label: 'Jendela',
            submenu: [{ role: 'minimize' }, { role: 'close' }],
        },
    ];

    Menu.setApplicationMenu(Menu.buildFromTemplate(template));
}

async function createWindow() {
    startServices();
    await waitForPort(8000);
    await waitForPort(3306);
    await waitForPort(4040, 10); // ngrok web interface - pastikan tunnel aktif

    const win = new BrowserWindow({
        width: 1366,
        height: 768,
        minWidth: 1024,
        minHeight: 640,
        title: 'Photobooth',
        icon: path.join(__dirname, 'icon.ico'),
        show: false,
        backgroundColor: '#0f172a',
        webPreferences: {
            nodeIntegration: false,
            contextIsolation: true,
            webSecurity: true,
        },
    });

    win.loadURL(serverUrl);
    win.once('ready-to-show', () => {
        win.show();
        win.focus();
    });
}

app.on('web-contents-created', (_event, contents) => {
    contents.session.setPermissionRequestHandler((_webContents, permission, callback) => {
        if (permission === 'media' || permission === 'mediaKeySystem') {
            callback(true);
            return;
        }
        callback(false);
    });

    contents.setWindowOpenHandler(({ url }) => {
        if (url.startsWith('http://127.0.0.1:8000')) {
            return { action: 'allow' };
        }
        shell.openExternal(url);
        return { action: 'deny' };
    });
});

app.whenReady().then(() => {
    buildMenu();
    createWindow();
});

app.on('window-all-closed', () => app.quit());
