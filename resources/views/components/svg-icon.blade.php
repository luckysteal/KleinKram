@props(['svg'])

<div {{ $attributes }} x-data="{ svgContent: '{{ str_replace(["\n", "\r"], "", $svg) }}' }" x-html="svgContent"
    x-init="
        $nextTick(() => {
            const svgElement = $el.querySelector('svg');
            if (svgElement) {
                svgElement.style.maxWidth = '100%';
                svgElement.style.maxHeight = '100%';
                svgElement.style.display = 'block';
            }
        });
    "
></div>
