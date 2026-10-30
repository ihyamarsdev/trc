{{--
    A4 preview of a template. The frame is a fixed 794 x 1123 px page (A4 at 96 dpi) that is scaled down
    to the width available, so it stays readable on phones and keeps the true proportions.
--}}
<div
    x-data="{
        s: 1,
        w: 794,
        h: 1123,
        fit() {
            const width = this.$refs.box.clientWidth

            if (width > 0) {
                this.s = Math.min(1, width / this.w)
            }
        },
    }"
    x-init="fit(); new ResizeObserver(() => fit()).observe($refs.box)"
    x-ref="box"
    style="width: 100%"
>
    <div
        x-bind:style="`position: relative; width: ${w * s}px; height: ${h * s}px; overflow: hidden; border: 1px solid #d1d5db; border-radius: 0.5rem; background: #fff; box-shadow: 0 1px 3px rgb(0 0 0 / 0.15)`"
        style="width: 100%; aspect-ratio: 794 / 1123; overflow: hidden; border: 1px solid #d1d5db; border-radius: 0.5rem; background: #fff"
    >
        <iframe
            title="Pratinjau dokumen"
            sandbox=""
            srcdoc="{{ $html }}"
            x-bind:style="`width: ${w}px; height: ${h}px; border: 0; transform: scale(${s}); transform-origin: 0 0`"
            style="width: 794px; height: 1123px; border: 0; transform-origin: 0 0"
        ></iframe>
    </div>
</div>
