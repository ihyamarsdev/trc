import preset from "../../../../vendor/filament/filament/tailwind.config.preset";

export default {
    presets: [preset],
    content: [
        "./app/Filament/User/**/*.php",
        "./resources/views/filament/user/**/*.blade.php",
        "./vendor/filament/**/*.blade.php",
    ],
    theme: {
    },
};
