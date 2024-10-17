import preset from './vendor/filament/support/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        "./resources/**/*.blade.php",
        './resources/views/vendor/invoices/templates/trc.blade.php'
    ],
}
