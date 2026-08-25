# RPP Kusmedios Deluxe

> Plugin de WordPress que extiende **Radio Player Page** con integración multi-plataforma, Now Playing en tiempo real, historial de canciones, PWA y más — construido para [estacionkusmedios.org](https://estacionkusmedios.org).

## 🎙️ Plataformas soportadas

| Plataforma | Now Playing | Historial | Auto-fill Stream URL |
|---|---|---|---|
| **Azuracast** | ✅ | ✅ | ✅ |
| **ZenoFM** | ✅ | ❌ | ✅ |
| **SonicPanel** | ✅ | ❌ | ✅ |
| **Shoutcast v2** | ✅ | ❌ | ✅ |
| **Icecast** | ✅ | ❌ | ✅ |
| **Manual / Otro** | ❌ | ❌ | ❌ |

## ⚡ Funciones Deluxe

- **Selector de plataforma** en el admin de WordPress (meta box en cada estación)
- **Prueba de conexión** en un clic desde el panel de admin
- **Auto-fill de Stream URL** — genera y aplica la URL del stream automáticamente al guardar
- **REST API proxy** (`/wp-json/rpkus/v1/nowplaying/{station_id}`) — evita CORS en el player del visitante
- **Endpoint de historial** (`/wp-json/rpkus/v1/history/{station_id}`) — últimas canciones de Azuracast
- **PWA** — genera `manifest.json` dinámico por estación para instalar como app en móvil
- **Chat embed** — campo para URL de Discord/Telegram/LiveChat
- **Botón de solicitudes** — enlace a WhatsApp/Telegram para pedir canciones

## 📦 Instalación

1. Asegúrate de tener instalado y activo **Radio Player Page** (v3.4.2+)
2. Descarga el `.zip` de este repositorio (Releases) o clona directamente en `wp-content/plugins/`
3. Activa el plugin desde el panel de WordPress
4. Ve a **RPP > Estaciones**, edita cualquier estación y verifica el nuevo meta box “Plataforma de Streaming”

## 🛠️ Requisitos

- WordPress 6.6+
- PHP 7.4+
- Plugin Radio Player Page 3.4.2+

## 📁 Estructura

```
rpp-kusmedios-deluxe/
├── rpp-kusmedios-deluxe.php       # Bootstrap principal
├── includes/
│   ├── platforms.php              # Definición de plataformas
│   ├── admin-metabox.php          # Meta box en admin
│   ├── rest-api.php               # Endpoints REST + normalización
│   ├── save-meta.php              # Guardado de configuración
│   ├── stream-autofill.php        # Auto-fill del stream URL en RPP
│   └── pwa.php                    # Manifest JSON dinámico
├── templates/
│   └── metabox.php                # HTML del meta box
├── assets/
│   ├── admin.css
│   └── admin.js
└── .github/workflows/
    └── release.yml                # Empaqueta ZIP en cada tag
```

## 🚀 CI/CD — Release automático

Cada vez que publicas un tag `v*.*.*` en GitHub, la Action `release.yml` empaqueta el plugin como `.zip` y lo adjunta al Release automáticamente.

```bash
git tag v1.0.1 && git push origin v1.0.1
```

## 📝 Notas de desarrollo

- Los metadatos Now Playing se cachean 13 segundos (vía WordPress Transients) para no sobrecargar la API del servidor.
- El historial de Azuracast se cachea 60 segundos.
- La PWA usa los campos de logo e imagen de la propia estación en RPP — no necesitas subir nada extra.

---

Hecho con ❤️ para **Estación Kusmedios** — Irapuato, México
