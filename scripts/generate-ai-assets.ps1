param(
    [string]$Root = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
)

Add-Type -AssemblyName System.Drawing

$imagesDir = Join-Path $Root 'public/assets/images'
$iconsDir = Join-Path $Root 'public/assets/icons'
New-Item -ItemType Directory -Force -Path $imagesDir, $iconsDir | Out-Null

function New-Brush($hex, [int]$alpha = 255) {
    $hex = $hex.TrimStart('#')
    $r = [Convert]::ToInt32($hex.Substring(0, 2), 16)
    $g = [Convert]::ToInt32($hex.Substring(2, 2), 16)
    $b = [Convert]::ToInt32($hex.Substring(4, 2), 16)
    return [System.Drawing.SolidBrush]::new([System.Drawing.Color]::FromArgb($alpha, $r, $g, $b))
}

function New-Pen($hex, [float]$width = 2, [int]$alpha = 255) {
    $hex = $hex.TrimStart('#')
    $r = [Convert]::ToInt32($hex.Substring(0, 2), 16)
    $g = [Convert]::ToInt32($hex.Substring(2, 2), 16)
    $b = [Convert]::ToInt32($hex.Substring(4, 2), 16)
    $pen = [System.Drawing.Pen]::new([System.Drawing.Color]::FromArgb($alpha, $r, $g, $b), $width)
    $pen.StartCap = [System.Drawing.Drawing2D.LineCap]::Round
    $pen.EndCap = [System.Drawing.Drawing2D.LineCap]::Round
    return $pen
}

function New-RoundedPath([float]$x, [float]$y, [float]$w, [float]$h, [float]$r) {
    $path = [System.Drawing.Drawing2D.GraphicsPath]::new()
    $d = $r * 2
    $path.AddArc($x, $y, $d, $d, 180, 90)
    $path.AddArc($x + $w - $d, $y, $d, $d, 270, 90)
    $path.AddArc($x + $w - $d, $y + $h - $d, $d, $d, 0, 90)
    $path.AddArc($x, $y + $h - $d, $d, $d, 90, 90)
    $path.CloseFigure()
    return $path
}

function Fill-Rounded($g, [float]$x, [float]$y, [float]$w, [float]$h, [float]$r, $brush) {
    $p = New-RoundedPath $x $y $w $h $r
    $g.FillPath($brush, $p)
    $p.Dispose()
}

function Stroke-Rounded($g, [float]$x, [float]$y, [float]$w, [float]$h, [float]$r, $pen) {
    $p = New-RoundedPath $x $y $w $h $r
    $g.DrawPath($pen, $p)
    $p.Dispose()
}

function New-Scene([int]$w, [int]$h, [string]$c1 = '#071020', [string]$c2 = '#6d28d9') {
    $bmp = [System.Drawing.Bitmap]::new($w, $h, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
    $g.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::AntiAliasGridFit
    $rect = [System.Drawing.Rectangle]::new(0, 0, $w, $h)
    $grad = [System.Drawing.Drawing2D.LinearGradientBrush]::new($rect, (New-Brush $c1).Color, (New-Brush $c2).Color, 35)
    $g.FillRectangle($grad, $rect)
    $grad.Dispose()
    return @{ Bitmap = $bmp; Graphics = $g }
}

function Save-Scene($scene, [string]$path) {
    $scene.Graphics.Dispose()
    $scene.Bitmap.Save($path, [System.Drawing.Imaging.ImageFormat]::Png)
    $scene.Bitmap.Dispose()
}

function Draw-Glow($g, [float]$x, [float]$y, [float]$r, [string]$hex, [int]$alpha = 80) {
    for ($i = 5; $i -ge 1; $i--) {
        $a = [int]($alpha / $i)
        $brush = New-Brush $hex $a
        $size = $r * $i / 2
        $g.FillEllipse($brush, $x - $size / 2, $y - $size / 2, $size, $size)
        $brush.Dispose()
    }
}

function Draw-StudyBot($g, [float]$cx, [float]$cy, [float]$scale = 1) {
    Draw-Glow $g $cx ($cy - 12 * $scale) (250 * $scale) '#38d5e8' 46
    $white = New-Brush '#f8fbff'
    $face = New-Brush '#07152e'
    $blue = New-Brush '#2563eb'
    $cyan = New-Brush '#38d5e8'
    $violet = New-Brush '#8b5cf6'
    $line = New-Pen '#bdefff' (6 * $scale) 210
    Fill-Rounded $g ($cx - 128 * $scale) ($cy - 148 * $scale) (256 * $scale) (190 * $scale) (70 * $scale) $white
    Fill-Rounded $g ($cx - 92 * $scale) ($cy - 105 * $scale) (184 * $scale) (96 * $scale) (42 * $scale) $face
    $g.DrawArc($line, $cx - 63 * $scale, $cy - 73 * $scale, 38 * $scale, 36 * $scale, 200, 140)
    $g.DrawArc($line, $cx + 25 * $scale, $cy - 73 * $scale, 38 * $scale, 36 * $scale, 200, 140)
    $g.FillPie($cyan, $cx - 25 * $scale, $cy - 43 * $scale, 50 * $scale, 38 * $scale, 20, 140)
    $g.FillEllipse((New-Brush '#eaf7ff'), $cx - 14 * $scale, $cy - 179 * $scale, 28 * $scale, 28 * $scale)
    $g.DrawLine((New-Pen '#1e2b4a' (7 * $scale)), $cx, $cy - 150 * $scale, $cx, $cy - 174 * $scale)
    $g.FillEllipse($blue, $cx - 174 * $scale, $cy - 86 * $scale, 52 * $scale, 82 * $scale)
    $g.FillEllipse($blue, $cx + 122 * $scale, $cy - 86 * $scale, 52 * $scale, 82 * $scale)
    $bookPen = New-Pen '#93c5fd' (5 * $scale) 230
    Fill-Rounded $g ($cx - 130 * $scale) ($cy + 12 * $scale) (124 * $scale) (108 * $scale) (12 * $scale) $blue
    Fill-Rounded $g ($cx + 6 * $scale) ($cy + 12 * $scale) (124 * $scale) (108 * $scale) (12 * $scale) $violet
    $g.DrawLine($bookPen, $cx - 103 * $scale, $cy + 40 * $scale, $cx - 25 * $scale, $cy + 40 * $scale)
    $g.DrawLine($bookPen, $cx + 31 * $scale, $cy + 40 * $scale, $cx + 105 * $scale, $cy + 40 * $scale)
    $g.DrawLine($bookPen, $cx - 103 * $scale, $cy + 64 * $scale, $cx - 39 * $scale, $cy + 64 * $scale)
    $g.DrawLine($bookPen, $cx + 31 * $scale, $cy + 64 * $scale, $cx + 95 * $scale, $cy + 64 * $scale)
}

function Draw-Document($g, [float]$x, [float]$y, [float]$s = 1) {
    $white = New-Brush '#f8fbff' 235
    $blue = New-Brush '#bfdbfe' 210
    Fill-Rounded $g $x $y (170*$s) (215*$s) (22*$s) $white
    $pen = New-Pen '#93c5fd' (7*$s) 220
    for ($i = 0; $i -lt 6; $i++) {
        $g.DrawLine($pen, $x + 32*$s, $y + (48 + $i*25)*$s, $x + (132 - ($i%2)*22)*$s, $y + (48 + $i*25)*$s)
    }
    $g.FillRectangle($blue, $x + 32*$s, $y + 148*$s, 62*$s, 18*$s)
}

function Draw-QuizCard($g, [float]$x, [float]$y, [float]$s = 1) {
    Fill-Rounded $g $x $y (230*$s) (176*$s) (28*$s) (New-Brush '#ffffff' 225)
    $cyan = New-Brush '#38d5e8'
    $violet = New-Brush '#8b5cf6'
    for ($i = 0; $i -lt 3; $i++) {
        $yy = $y + (38 + $i*42)*$s
        $g.FillEllipse($(if ($i -eq 2) { $violet } else { $cyan }), $x + 30*$s, $yy, 22*$s, 22*$s)
        $g.DrawLine((New-Pen '#6366f1' (7*$s) 190), $x + 70*$s, $yy + 11*$s, $x + (185 - $i*18)*$s, $yy + 11*$s)
    }
}

function Draw-Chat($g, [float]$x, [float]$y, [float]$s = 1) {
    Fill-Rounded $g $x $y (210*$s) (128*$s) (34*$s) (New-Brush '#38d5e8' 224)
    $tail = [System.Drawing.PointF[]]@(
        [System.Drawing.PointF]::new($x + 58*$s, $y + 116*$s),
        [System.Drawing.PointF]::new($x + 36*$s, $y + 160*$s),
        [System.Drawing.PointF]::new($x + 91*$s, $y + 124*$s)
    )
    $g.FillPolygon((New-Brush '#38d5e8' 224), $tail)
    $dot = New-Brush '#ffffff' 235
    $g.FillEllipse($dot, $x + 58*$s, $y + 56*$s, 18*$s, 18*$s)
    $g.FillEllipse($dot, $x + 98*$s, $y + 56*$s, 18*$s, 18*$s)
    $g.FillEllipse($dot, $x + 138*$s, $y + 56*$s, 18*$s, 18*$s)
}

function Draw-Upload($g, [float]$x, [float]$y, [float]$s = 1) {
    Fill-Rounded $g $x $y (280*$s) (210*$s) (34*$s) (New-Brush '#ffffff' 220)
    Draw-Glow $g ($x + 140*$s) ($y + 100*$s) (180*$s) '#38d5e8' 38
    $cloudPen = New-Pen '#60a5fa' (10*$s) 225
    $g.DrawArc($cloudPen, $x + 62*$s, $y + 84*$s, 72*$s, 58*$s, 195, 170)
    $g.DrawArc($cloudPen, $x + 112*$s, $y + 62*$s, 84*$s, 72*$s, 195, 170)
    $g.DrawArc($cloudPen, $x + 174*$s, $y + 90*$s, 58*$s, 50*$s, 195, 170)
    $g.DrawLine($cloudPen, $x + 108*$s, $y + 136*$s, $x + 220*$s, $y + 136*$s)
    $arrow = New-Pen '#8b5cf6' (12*$s) 235
    $g.DrawLine($arrow, $x + 142*$s, $y + 138*$s, $x + 142*$s, $y + 88*$s)
    $g.DrawLine($arrow, $x + 142*$s, $y + 88*$s, $x + 118*$s, $y + 112*$s)
    $g.DrawLine($arrow, $x + 142*$s, $y + 88*$s, $x + 166*$s, $y + 112*$s)
}

function Draw-Calendar($g, [float]$x, [float]$y, [float]$s = 1) {
    Fill-Rounded $g $x $y (260*$s) (220*$s) (28*$s) (New-Brush '#ffffff' 225)
    Fill-Rounded $g $x $y (260*$s) (58*$s) (28*$s) (New-Brush '#60a5fa' 230)
    $pen = New-Pen '#c7d2fe' (3*$s) 185
    for ($r = 0; $r -lt 3; $r++) {
        for ($c = 0; $c -lt 4; $c++) {
            Stroke-Rounded $g ($x + (28 + $c*52)*$s) ($y + (82 + $r*42)*$s) (34*$s) (26*$s) (8*$s) $pen
        }
    }
    Fill-Rounded $g ($x + 28*$s) ($y + 82*$s) (34*$s) (26*$s) (8*$s) (New-Brush '#38d5e8' 220)
    Fill-Rounded $g ($x + 132*$s) ($y + 124*$s) (86*$s) (26*$s) (8*$s) (New-Brush '#8b5cf6' 205)
}

function Add-Title($g, [string]$title, [string]$subtitle, [int]$w, [int]$h) {
    $fontTitle = [System.Drawing.Font]::new('Segoe UI', [Math]::Max(28, $w / 22), [System.Drawing.FontStyle]::Bold)
    $fontSub = [System.Drawing.Font]::new('Segoe UI', [Math]::Max(15, $w / 44), [System.Drawing.FontStyle]::Regular)
    $sf = [System.Drawing.StringFormat]::new()
    $sf.Alignment = [System.Drawing.StringAlignment]::Center
    $g.DrawString($title, $fontTitle, (New-Brush '#ffffff'), [System.Drawing.RectangleF]::new(0, $h - 190, $w, 70), $sf)
    $g.DrawString($subtitle, $fontSub, (New-Brush '#cbd5e1'), [System.Drawing.RectangleF]::new(40, $h - 120, $w - 80, 56), $sf)
}

function New-FeatureAsset([string]$name, [string]$title, [scriptblock]$draw) {
    $scene = New-Scene 1024 768 '#0b1020' '#4338ca'
    $g = $scene.Graphics
    Draw-Glow $g 740 160 360 '#38d5e8' 36
    Draw-Glow $g 180 620 320 '#a855f7' 42
    & $draw $g
    Add-Title $g $title 'AI Study Buddy' 1024 768
    Save-Scene $scene (Join-Path $imagesDir $name)
}

# Logo and app icon.
$scene = New-Scene 1024 1024 '#070b18' '#4f46e5'
Draw-Glow $scene.Graphics 512 440 620 '#38d5e8' 58
Draw-StudyBot $scene.Graphics 512 478 1.45
Add-Title $scene.Graphics 'AI Study Buddy' 'Belajar cerdas dengan AI' 1024 1024
Save-Scene $scene (Join-Path $imagesDir 'logo-ai-study-buddy.png')

$scene = New-Scene 512 512 '#071020' '#6d28d9'
Draw-Glow $scene.Graphics 256 250 420 '#38d5e8' 54
Draw-StudyBot $scene.Graphics 256 258 .78
Save-Scene $scene (Join-Path $imagesDir 'app-icon.png')
$scene = New-Scene 512 512 '#071020' '#6d28d9'
Draw-Glow $scene.Graphics 256 250 420 '#38d5e8' 54
Draw-StudyBot $scene.Graphics 256 258 .78
Save-Scene $scene (Join-Path $iconsDir 'app-icon.png')

# Landing hero.
$scene = New-Scene 1600 900 '#050816' '#1d1640'
$g = $scene.Graphics
Draw-Glow $g 1170 250 720 '#38d5e8' 44
Draw-Glow $g 280 760 520 '#a855f7' 36
Draw-Document $g 130 170 1.35
Draw-QuizCard $g 1150 150 1.15
Draw-Chat $g 1140 540 1.05
Draw-Upload $g 150 550 1.05
Draw-StudyBot $g 810 440 1.38
Save-Scene $scene (Join-Path $imagesDir 'hero-ai-study.png')

New-FeatureAsset 'feature-summarizer.png' 'AI Summarizer' { param($g) Draw-Document $g 140 120 1.35; Draw-StudyBot $g 680 260 .82 }
New-FeatureAsset 'feature-quiz.png' 'Quiz Generator' { param($g) Draw-QuizCard $g 132 128 1.55; Draw-StudyBot $g 700 276 .82 }
New-FeatureAsset 'feature-chat.png' 'Chat with Material' { param($g) Draw-Chat $g 120 130 1.45; Draw-Document $g 610 120 1.08; Draw-StudyBot $g 520 365 .72 }
New-FeatureAsset 'feature-upload.png' 'File Upload' { param($g) Draw-Upload $g 122 122 1.65; Draw-Document $g 690 160 .92 }
New-FeatureAsset 'feature-study-plan.png' 'Study Plan' { param($g) Draw-Calendar $g 112 120 1.55; Draw-StudyBot $g 710 300 .76 }

$scene = New-Scene 900 650 '#f8fafc' '#e0e7ff'
$g = $scene.Graphics
Draw-Glow $g 450 260 420 '#60a5fa' 28
Fill-Rounded $g 245 130 410 250 42 (New-Brush '#ffffff' 230)
Stroke-Rounded $g 245 130 410 250 42 (New-Pen '#c7d2fe' 4 220)
Draw-StudyBot $g 450 275 .62
$font = [System.Drawing.Font]::new('Segoe UI', 34, [System.Drawing.FontStyle]::Bold)
$sub = [System.Drawing.Font]::new('Segoe UI', 17, [System.Drawing.FontStyle]::Regular)
$sf = [System.Drawing.StringFormat]::new()
$sf.Alignment = [System.Drawing.StringAlignment]::Center
$g.DrawString('Belum ada data', $font, (New-Brush '#0f172a'), [System.Drawing.RectangleF]::new(0, 430, 900, 55), $sf)
$g.DrawString('Mulai upload materi atau buat hasil AI pertama kamu.', $sub, (New-Brush '#64748b'), [System.Drawing.RectangleF]::new(80, 490, 740, 40), $sf)
Save-Scene $scene (Join-Path $imagesDir 'empty-state.png')

$scene = New-Scene 512 512 '#08111f' '#4338ca'
Draw-Glow $scene.Graphics 256 256 420 '#38d5e8' 52
Draw-StudyBot $scene.Graphics 256 265 .8
Save-Scene $scene (Join-Path $imagesDir 'avatar-ai.png')

$scene = New-Scene 512 512 '#eef2ff' '#c7d2fe'
$g = $scene.Graphics
Draw-Glow $g 256 230 350 '#60a5fa' 32
$g.FillEllipse((New-Brush '#ffffff' 235), 116, 88, 280, 280)
$g.FillEllipse((New-Brush '#4f46e5' 235), 184, 130, 144, 144)
Fill-Rounded $g 112 330 288 150 68 (New-Brush '#0f172a' 225)
$g.FillEllipse((New-Brush '#bfdbfe' 240), 208, 162, 28, 28)
$g.FillEllipse((New-Brush '#bfdbfe' 240), 284, 162, 28, 28)
$g.DrawArc((New-Pen '#bfdbfe' 8 240), 218, 200, 94, 55, 20, 140)
Save-Scene $scene (Join-Path $imagesDir 'avatar-user.png')

$fallbackSvg = @'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" role="img" aria-label="AI Study Buddy fallback">
  <defs>
    <linearGradient id="g" x1="0" x2="1" y1="0" y2="1">
      <stop offset="0" stop-color="#38d5e8"/>
      <stop offset=".55" stop-color="#4f46e5"/>
      <stop offset="1" stop-color="#a855f7"/>
    </linearGradient>
  </defs>
  <rect width="512" height="512" rx="112" fill="#071020"/>
  <circle cx="256" cy="246" r="150" fill="url(#g)" opacity=".9"/>
  <rect x="158" y="174" width="196" height="112" rx="42" fill="#07152e"/>
  <path d="M213 226c18-20 38-20 56 0M296 226c18-20 38-20 56 0" fill="none" stroke="#bdefff" stroke-width="18" stroke-linecap="round"/>
  <path d="M220 326h112l-56 48z" fill="#fff" opacity=".92"/>
</svg>
'@
Set-Content -Path (Join-Path $iconsDir 'fallback-illustration.svg') -Value $fallbackSvg -Encoding UTF8

Write-Host "Generated AI Study Buddy visual assets in $imagesDir"
