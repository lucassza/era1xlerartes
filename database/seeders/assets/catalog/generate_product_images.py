#!/usr/bin/env python3
"""Gera as ilustracoes de demonstracao dos produtos do catalogo.

Sao ilustracoes vetoriais planas na identidade Era1x Ler Artes (vinho #650010,
rosa #e95d78, tons de apoio), nao fotos reais: servem ao CatalogDemoSeeder ate
que as fotos de catalogo entrem pelo painel.

Uso: python3 database/seeders/assets/catalog/generate_product_images.py
"""

from __future__ import annotations

import math
import os

from PIL import Image, ImageDraw

SIZE = 1200
SS = 3  # supersampling
S = SIZE * SS

OUT_DIR = os.path.dirname(os.path.abspath(__file__))

WINE = "#650010"
WINE_STRONG = "#820018"
PINK = "#e95d78"
PINK_SOFT = "#f7b8c6"
INK = "#2f2f35"
PAPER = "#ffffff"

TINT_WINE = "#f7edee"
TINT_PINK = "#fdf1f4"
TINT_WARM = "#f5f2f0"

LINE = 9 * SS  # espessura padrao do contorno


def px(v: float) -> int:
    return int(round(v * SS))


def new_canvas(tint: str) -> tuple[Image.Image, ImageDraw.ImageDraw]:
    img = Image.new("RGB", (S, S), tint)
    return img, ImageDraw.Draw(img)


def shadow(d: ImageDraw.ImageDraw, cx: float, cy: float, rx: float, ry: float) -> None:
    d.ellipse(
        [px(cx - rx), px(cy - ry), px(cx + rx), px(cy + ry)],
        fill="#eadfe0",
    )


def rr(d, box, radius, fill=None, outline=WINE, width=LINE):
    d.rounded_rectangle(
        [px(box[0]), px(box[1]), px(box[2]), px(box[3])],
        radius=px(radius),
        fill=fill,
        outline=outline,
        width=width,
    )


def ell(d, box, fill=None, outline=WINE, width=LINE):
    d.ellipse(
        [px(box[0]), px(box[1]), px(box[2]), px(box[3])],
        fill=fill,
        outline=outline,
        width=width,
    )


def poly(d, points, fill=None, outline=WINE, width=LINE):
    pts = [(px(x), px(y)) for x, y in points]
    d.polygon(pts, fill=fill, outline=outline, width=width)


def line(d, points, fill=WINE, width=LINE):
    pts = [(px(x), px(y)) for x, y in points]
    d.line(pts, fill=fill, width=width, joint="curve")


def save(img: Image.Image, name: str) -> None:
    out = img.resize((SIZE, SIZE), Image.LANCZOS)
    path = os.path.join(OUT_DIR, name)
    out.save(path, optimize=True)
    print(f"{name} — {os.path.getsize(path) // 1024} KB")


# ---------------------------------------------------------------- ilustracoes


def tinta_acrilica() -> Image.Image:
    img, d = new_canvas(TINT_WINE)
    shadow(d, 600, 962, 250, 40)
    # corpo da bisnaga
    rr(d, (485, 320, 715, 880), 26, fill=PAPER)
    # rotulo impresso
    d.rectangle([px(485), px(470), px(715), px(790)], fill=PINK)
    rr(d, (555, 545, 645, 585), 12, fill=PAPER, outline=None, width=0)
    rr(d, (525, 625, 675, 700), 14, fill=WINE, outline=None, width=0)
    rr(d, (485, 320, 715, 880), 26, fill=None)
    line(d, [(485, 470), (715, 470)])
    line(d, [(485, 790), (715, 790)])
    # base prensada
    d.rectangle([px(465), px(862), px(735), px(946)], fill=PAPER)
    d.rectangle([px(465), px(862), px(735), px(946)], outline=WINE, width=LINE)
    for x in (520, 575, 630, 685):
        line(d, [(x, 872), (x, 936)], width=5 * SS)
    # ombro e gargalo
    poly(d, [(485, 322), (715, 322), (655, 232), (545, 232)], fill=PAPER)
    d.rectangle([px(545), px(200), px(655), px(240)], fill="#e6dedc")
    d.rectangle([px(545), px(200), px(655), px(240)], outline=WINE, width=LINE)
    # tampa rosqueada
    rr(d, (552, 108, 648, 208), 12, fill=WINE, outline=WINE)
    for y in (132, 156, 180):
        line(d, [(560, y), (640, y)], fill=TINT_WINE, width=5 * SS)
    return img


def aquarela_12() -> Image.Image:
    img, d = new_canvas(TINT_PINK)
    shadow(d, 600, 900, 330, 44)
    rr(d, (250, 380, 950, 880), 34, fill=PAPER)
    # tampa aberta ao fundo
    rr(d, (285, 250, 915, 400), 26, fill="#f3f3f4")
    rr(d, (250, 380, 950, 880), 34, fill=None)
    pans = [
        "#c8443f", "#e07a3c", "#e6b73f", "#7ba05b",
        "#3f8f7a", "#3f6fa0", "#5b4f9c", "#8c4a86",
        WINE, PINK, "#6b4b33", INK,
    ]
    x0, y0, w, h, gap = 300, 440, 140, 170, 12
    for i, color in enumerate(pans):
        col, row = i % 4, i // 4
        x = x0 + col * (w + gap + 16)
        y = y0 + row * (h + gap)
        rr(d, (x, y, x + w, y + h), 16, fill=color, outline=WINE, width=6 * SS)
    return img


def pinceis() -> Image.Image:
    img, d = new_canvas(TINT_WARM)
    shadow(d, 600, 966, 300, 38)
    # cinco pinceis, leque suave, cerdas para cima
    specs = [
        (300, -26, "#b5793f", "chata"),
        (410, -13, WINE, "redonda"),
        (520, 0, "#3f6fa0", "chata"),
        (630, 13, "#7ba05b", "leque"),
        (740, 26, "#8c4a86", "redonda"),
    ]
    for x, tilt, color, kind in specs:
        top = 250 + abs(tilt) * 1.2
        # cabo: largo na virola, afinando ate a ponta arredondada
        poly(
            d,
            [
                (x - 26 + tilt * 0.2, top),
                (x + 26 + tilt * 0.2, top),
                (x + 17 + tilt, 890),
                (x - 17 + tilt, 890),
            ],
            fill=color,
        )
        ell(d, (x - 17 + tilt, 866, x + 17 + tilt, 934), fill=color)
        # virola metalica
        poly(
            d,
            [
                (x - 27, top - 96),
                (x + 27, top - 96),
                (x + 26 + tilt * 0.2, top + 6),
                (x - 26 + tilt * 0.2, top + 6),
            ],
            fill="#d8d4d2",
        )
        line(d, [(x - 25, top - 60), (x + 25, top - 60)], width=5 * SS)
        # cerdas
        if kind == "chata":
            poly(
                d,
                [
                    (x - 27, top - 96),
                    (x + 27, top - 96),
                    (x + 33, top - 230),
                    (x - 33, top - 230),
                ],
                fill="#efe3d6",
            )
        elif kind == "redonda":
            poly(
                d,
                [
                    (x - 27, top - 96),
                    (x + 27, top - 96),
                    (x + 30, top - 190),
                    (x, top - 246),
                    (x - 30, top - 190),
                ],
                fill="#efe3d6",
            )
        else:  # leque
            poly(
                d,
                [
                    (x - 27, top - 96),
                    (x + 27, top - 96),
                    (x + 62, top - 224),
                    (x - 62, top - 224),
                ],
                fill="#efe3d6",
            )
            for dx in (-34, 0, 34):
                line(
                    d,
                    [(x + dx * 0.4, top - 110), (x + dx, top - 214)],
                    fill="#d9c8b4",
                    width=5 * SS,
                )
    return img


def tela_canvas() -> Image.Image:
    img, d = new_canvas(TINT_PINK)
    shadow(d, 600, 958, 300, 38)
    # profundidade do chassi (lateral esquerda e base)
    poly(d, [(300, 300), (360, 250), (360, 880), (300, 930)], fill="#d9c8b4")
    poly(d, [(300, 930), (360, 880), (900, 880), (840, 930)], fill="#c9a468")
    # face da tela
    rr(d, (360, 250, 900, 880), 8, fill="#f7f4ef")
    # trama do algodao
    for i in range(7):
        y = 300 + i * 84
        line(d, [(372, y), (888, y)], fill="#f1ece5", width=3 * SS)
    rr(d, (360, 250, 900, 880), 8, fill=None)
    # grampos do chassi na dobra
    for y in (330, 500, 670, 820):
        line(d, [(318, y), (344, y - 18)], fill=WINE, width=6 * SS)
    return img


def skein(color: str, angle: float) -> Image.Image:
    """Uma meada de linha, desenhada isolada para poder ser girada."""
    w, h = px(420), px(150)
    layer = Image.new("RGBA", (w, h), (0, 0, 0, 0))
    dl = ImageDraw.Draw(layer)
    body = [px(10), px(34), px(410), px(116)]
    dl.rounded_rectangle(body, radius=px(41), fill=color, outline=WINE, width=LINE)
    # fios visiveis ao longo da meada
    for y in (px(56), px(75), px(94)):
        dl.line([(px(58), y), (px(362), y)], fill="#ffffff44", width=4 * SS)
    # cintas de papel
    for x in (px(96), px(268)):
        dl.rounded_rectangle(
            [x, px(22), x + px(56), px(128)], radius=px(10), fill=PAPER, outline=WINE, width=LINE
        )
        dl.line([(x + px(28), px(30)), (x + px(28), px(120))], fill=WINE, width=4 * SS)
    return layer.rotate(angle, resample=Image.BICUBIC, expand=True)


def linhas_bordado() -> Image.Image:
    img, d = new_canvas(TINT_WINE)
    shadow(d, 600, 962, 330, 40)
    rows = [
        [("#c8443f", 4), ("#e6b73f", -3)],
        [("#7ba05b", -5), ("#3f6fa0", 3)],
        [("#5b4f9c", 3), (PINK, -4)],
    ]
    for r, pair in enumerate(rows):
        for c, (color, angle) in enumerate(pair):
            layer = skein(color, angle)
            img.paste(layer, (px(150 + c * 430), px(250 + r * 220)), layer)
    return img


def bastidor() -> Image.Image:
    img, d = new_canvas(TINT_WARM)
    shadow(d, 600, 960, 290, 40)
    # tecido
    ell(d, (250, 250, 950, 950), fill="#f6f1e9")
    # aro externo
    ell(d, (222, 222, 978, 978), fill=None, outline="#c9a468", width=22 * SS)
    ell(d, (222, 222, 978, 978), fill=None, outline=WINE, width=6 * SS)
    ell(d, (270, 270, 930, 930), fill=None, outline=WINE, width=5 * SS)
    # parafuso no topo
    rr(d, (540, 150, 660, 250), 18, fill="#c9a468")
    ell(d, (562, 120, 638, 196), fill="#d8d4d2")
    # bordado: flor com folhas
    for a in range(0, 360, 72):
        rad = math.radians(a)
        ell(
            d,
            (
                600 + 92 * math.cos(rad) - 62,
                600 + 92 * math.sin(rad) - 62,
                600 + 92 * math.cos(rad) + 62,
                600 + 92 * math.sin(rad) + 62,
            ),
            fill=PINK,
            outline=WINE,
            width=5 * SS,
        )
    ell(d, (546, 546, 654, 654), fill=WINE, outline=WINE)
    line(d, [(600, 700), (600, 850)], fill="#5f7f47", width=10 * SS)
    for side in (-1, 1):
        folha = leaf_layer("#7ba05b", 150, 66, -28 * side)
        img.paste(folha, (px(600 if side > 0 else 450), px(740)), folha)
    return img


def macrame() -> Image.Image:
    img, d = new_canvas(TINT_PINK)
    shadow(d, 600, 966, 300, 38)
    cord, cord_edge = "#efe3d6", "#d9c8b4"

    # vareta de madeira
    rr(d, (270, 170, 930, 226), 28, fill="#c9a468")
    ell(d, (252, 170, 308, 226), fill="#c9a468")
    ell(d, (892, 170, 948, 226), fill="#c9a468")

    def strand(a, y0, b, y1):
        line(d, [(a, y0), (b, y1)], fill=cord, width=30 * SS)
        line(d, [(a, y0), (b, y1)], fill=cord_edge, width=4 * SS)

    xs = [330, 430, 530, 630, 730, 830]
    for x in xs:
        strand(x, 220, x, 320)

    # rede de nos: duas fileiras de losangos
    for row, (y0, y1) in enumerate([(320, 470), (470, 620)]):
        pairs = [(330, 430), (530, 630), (730, 830)] if row == 0 else [(430, 530), (630, 730)]
        if row == 1:
            strand(330, y0, 330, y1)
            strand(830, y0, 830, y1)
        for a, b in pairs:
            mid = (a + b) / 2
            strand(a, y0, mid, y1)
            strand(b, y0, mid, y1)
            rr(d, (mid - 40, y1 - 46, mid + 40, y1 + 22), 20, fill=cord, outline=WINE, width=5 * SS)
            line(d, [(mid - 26, y1 - 12), (mid + 26, y1 - 12)], fill=cord_edge, width=5 * SS)

    # franja aberta
    for i, x in enumerate(xs):
        strand(x, 620, x + (i - 2.5) * 22, 840)

    # novelo de corda apoiado na base
    ell(d, (760, 760, 990, 966), fill=cord)
    for a in range(-70, 71, 24):
        rad = math.radians(a)
        line(
            d,
            [
                (875 + 112 * math.cos(rad), 863 + 100 * math.sin(rad)),
                (875 - 112 * math.cos(rad), 863 - 100 * math.sin(rad)),
            ],
            fill=cord_edge,
            width=5 * SS,
        )
    ell(d, (760, 760, 990, 966), fill=None)
    return img


def papel_scrapbook() -> Image.Image:
    img, d = new_canvas(TINT_WARM)
    shadow(d, 600, 960, 320, 42)
    # folhas empilhadas
    for i, color in enumerate(["#e8dcd2", "#f2e3e6", PAPER]):
        off = (2 - i) * 34
        rr(d, (250 + off, 250 + off, 890 + off, 890 + off), 16, fill=color)
    # estampa floral na folha de cima
    for row in range(3):
        for col in range(3):
            cx = 400 + col * 190
            cy = 400 + row * 190
            for a in range(0, 360, 72):
                rad = math.radians(a)
                ell(
                    d,
                    (
                        cx + 46 * math.cos(rad) - 34,
                        cy + 46 * math.sin(rad) - 34,
                        cx + 46 * math.cos(rad) + 34,
                        cy + 46 * math.sin(rad) + 34,
                    ),
                    fill=PINK_SOFT,
                    outline=PINK,
                    width=4 * SS,
                )
            ell(d, (cx - 26, cy - 26, cx + 26, cy + 26), fill=WINE, outline=WINE)
    return img


def leaf_layer(color: str, w: float, h: float, angle: float) -> Image.Image:
    """Folha em forma de lente, desenhada isolada para poder ser girada."""
    layer = Image.new("RGBA", (px(w) + 4, px(h) + 4, ), (0, 0, 0, 0))
    dl = ImageDraw.Draw(layer)
    steps = 60
    top = [(px(w) * t / steps, px(h) / 2 - px(h) / 2 * math.sin(math.pi * t / steps)) for t in range(steps + 1)]
    bottom = [(px(w) * t / steps, px(h) / 2 + px(h) / 2 * math.sin(math.pi * t / steps)) for t in range(steps, -1, -1)]
    dl.polygon(top + bottom, fill=color, outline=WINE, width=6 * SS)
    dl.line([(px(6), px(h) / 2), (px(w - 6), px(h) / 2)], fill=WINE, width=5 * SS)
    return layer.rotate(angle, resample=Image.BICUBIC, expand=True)


def carimbos() -> Image.Image:
    img, d = new_canvas(TINT_PINK)
    shadow(d, 600, 958, 310, 38)
    # bloco acrilico ao fundo
    rr(d, (250, 260, 690, 700), 20, fill="#e8f0f4")
    rr(d, (286, 296, 654, 664), 12, fill="#f4f9fb", outline="#cadde6", width=6 * SS)
    line(d, [(300, 330), (470, 330)], fill="#cadde6", width=7 * SS)
    # carimbo de silicone aderido ao bloco: folha
    folha = leaf_layer(WINE, 300, 130, 32)
    img.paste(folha, (px(330), px(360)), folha)
    # carimbo solto na frente: flor
    rr(d, (620, 560, 950, 890), 20, fill=PAPER)
    for a in range(0, 360, 60):
        rad = math.radians(a)
        ell(
            d,
            (
                785 + 78 * math.cos(rad) - 50,
                725 + 78 * math.sin(rad) - 50,
                785 + 78 * math.cos(rad) + 50,
                725 + 78 * math.sin(rad) + 50,
            ),
            fill=PINK_SOFT,
            outline=WINE,
            width=6 * SS,
        )
    ell(d, (741, 681, 829, 769), fill=WINE, outline=WINE)
    return img


def massa_biscuit() -> Image.Image:
    img, d = new_canvas(TINT_WARM)
    shadow(d, 600, 950, 300, 42)
    # pote
    rr(d, (350, 380, 850, 900), 40, fill=PAPER)
    rr(d, (320, 290, 880, 410), 30, fill="#e6dedc")
    rr(d, (350, 380, 850, 900), 40, fill=None)
    # rotulo
    rr(d, (410, 520, 790, 780), 20, fill=TINT_WINE)
    rr(d, (455, 575, 745, 640), 16, fill=WINE, outline=None, width=0)
    line(d, [(455, 700), (745, 700)], fill=PINK, width=8 * SS)
    # massa modelada ao lado
    ell(d, (190, 780, 400, 920), fill="#f4f1ec")
    ell(d, (245, 720, 375, 830), fill="#f4f1ec")
    return img


def cola_decoupage() -> Image.Image:
    img, d = new_canvas(TINT_WINE)
    shadow(d, 600, 950, 270, 40)
    # frasco
    rr(d, (430, 380, 770, 900), 40, fill=PAPER)
    # gargalo e bico
    rr(d, (540, 250, 660, 400), 20, fill=PAPER)
    poly(d, [(548, 250), (652, 250), (630, 150), (570, 150)], fill=WINE)
    # rotulo
    rr(d, (430, 520, 770, 790), 0, fill=PINK_SOFT, outline=None, width=0)
    rr(d, (430, 380, 770, 900), 40, fill=None)
    line(d, [(430, 520), (770, 520)])
    line(d, [(430, 790), (770, 790)])
    rr(d, (490, 580, 710, 640), 14, fill=WINE, outline=None, width=0)
    line(d, [(490, 710), (710, 710)], fill=WINE, width=7 * SS)
    return img


def aquarela_perola() -> Image.Image:
    img, d = new_canvas(TINT_PINK)
    shadow(d, 600, 920, 340, 44)
    rr(d, (200, 330, 1000, 880), 34, fill="#3a3a42")
    pans = [
        "#d9c7a8", "#cbb7c9", "#a8c2cf", "#b9cdb5", "#e0cdb0", "#cfc0d6",
        "#c8a9a0", "#a9b6c8", "#d6c9a3", "#bfd0c6", "#d3b5c2", "#b6aecb",
        "#e2d3bb", "#c2cfd8", "#d8c3ae", "#b3c9bb", "#cdb9cb", "#adb9cd",
        "#dbc9b4", "#c6d3cc", "#d2bcc6", "#b8b2c9", "#e0d6c4", "#c9d4dc",
    ]
    x0, y0, w, h, gapx, gapy = 250, 385, 105, 105, 20, 22
    for i, color in enumerate(pans):
        col, row = i % 6, i // 6
        x = x0 + col * (w + gapx)
        y = y0 + row * (h + gapy)
        ell(d, (x, y, x + w, y + h), fill=color, outline="#26262c", width=5 * SS)
    return img


IMAGES = {
    "tinta-acrilica-premium-250ml.png": tinta_acrilica,
    "aquarela-profissional-12-cores.png": aquarela_12,
    "kit-12-pinceis-cerdas-sinteticas.png": pinceis,
    "tela-canvas-40x50cm.png": tela_canvas,
    "kit-linhas-de-bordado-100-algodao-50-cores.png": linhas_bordado,
    "bastidor-de-bambu-25cm.png": bastidor,
    "kit-macrame-iniciante-completo.png": macrame,
    "papel-scrapbook-floral-premium-30x30cm-30-folhas.png": papel_scrapbook,
    "kit-carimbos-de-silicone-colecao-flora.png": carimbos,
    "massa-para-biscuit-branca-500g.png": massa_biscuit,
    "cola-para-decoupage-250ml-fosca.png": cola_decoupage,
    "aquarela-perola-24-cores-fora-de-linha.png": aquarela_perola,
}


if __name__ == "__main__":
    for filename, factory in IMAGES.items():
        save(factory(), filename)
