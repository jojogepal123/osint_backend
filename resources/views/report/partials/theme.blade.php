@php
// ══════════════════════════════════════════════════════════════════
//  DRASHTA REPORT COLOR THEME
//  Change ONLY these values to update the theme across ALL reports.
// ══════════════════════════════════════════════════════════════════
$clr = [
    'primary'     => '#0f3460',   // Navy  — header bg, card heads, title text, table keys
    'accent'      => '#a8ff78',   // Lime  — accent bar, section borders, brand name, card-head text
    'accentBg'    => '#eaf4fb',   // Pale blue — subject banner bg, odd table rows, card body
    'metaText'    => '#b0c4de',   // Muted blue — header tagline & date text
    'bodyText'    => '#1a1a2e',   // Near-black — body text
    'valueText'   => '#333333',   // Dark grey — table value text
    'rowBorder'   => '#e8eaf0',   // Light grey — table row borders
    'cardBorder'  => '#d0dce8',   // Light blue-grey — card outlines
    'pillGreenBg' => '#dcfce7',   // Active/yes pill background
    'pillGreenTx' => '#166534',   // Active/yes pill text
    'pillGreenBd' => '#bbf7d0',   // Active/yes pill border
    'pillRedBg'   => '#fee2e2',   // Inactive/no pill background
    'pillRedTx'   => '#991b1b',   // Inactive/no pill text
    'pillRedBd'   => '#fecaca',   // Inactive/no pill border
    'skillBg'     => '#e8f0fe',   // Skill pill background
    'skillBd'     => '#c5d8fb',   // Skill pill border
    'white'       => '#ffffff',
];
@endphp
<style>
  /* ── Reset ── */
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: {{ $clr['bodyText'] }}; background: {{ $clr['white'] }}; }

  /* ── Header ── */
  .rpt-header { background: {{ $clr['primary'] }}; color: {{ $clr['white'] }}; padding: 18px 24px 14px; }
  .rpt-header-top { display: table; width: 100%; }
  .rpt-brand { display: table-cell; vertical-align: middle; }
  .rpt-app-name { font-size: 22px; font-weight: bold; letter-spacing: 3px; color: {{ $clr['accent'] }}; }
  .rpt-tagline { font-size: 9px; color: {{ $clr['metaText'] }}; letter-spacing: 1px; margin-top: 2px; }
  .rpt-meta { display: table-cell; vertical-align: middle; text-align: right; }
  .rpt-type { font-size: 13px; font-weight: bold; color: {{ $clr['accent'] }}; }
  .rpt-date { font-size: 9px; color: {{ $clr['metaText'] }}; margin-top: 2px; }

  /* ── Accent bar ── */
  .rpt-accent-bar { height: 4px; background: {{ $clr['accent'] }}; }

  /* ── Subject banner ── */
  .rpt-subject { background: {{ $clr['accentBg'] }}; border-left: 5px solid {{ $clr['primary'] }}; padding: 12px 20px; margin: 16px 24px; }
  .rpt-subject-label { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: {{ $clr['primary'] }}; margin-bottom: 3px; }
  .rpt-subject-value { font-size: 16px; font-weight: bold; color: {{ $clr['primary'] }}; }

  /* ── Section title ── */
  .section-title {
    font-size: 13px; font-weight: bold; text-transform: uppercase;
    letter-spacing: 1.5px; color: {{ $clr['primary'] }};
    border-bottom: 2px solid {{ $clr['accent'] }}; padding-bottom: 4px; margin-bottom: 8px;
  }

  /* ── Tables ── */
  table { width: 100%; border-collapse: collapse; }
  tr:nth-child(odd)  td, tr:nth-child(odd)  th { background: {{ $clr['accentBg'] }}; }
  tr:nth-child(even) td, tr:nth-child(even) th { background: {{ $clr['white'] }}; }
  td, th { padding: 6px 12px; border-bottom: 1px solid {{ $clr['rowBorder'] }}; vertical-align: top; font-size: 11px; }
  th { width: 36%; font-weight: bold; color: {{ $clr['primary'] }}; text-align: left; white-space: nowrap; }
  td { color: {{ $clr['valueText'] }}; }
  td a { color: {{ $clr['primary'] }}; text-decoration: underline; }

  /* ── Cards ── */
  .card { border: 1px solid {{ $clr['cardBorder'] }}; border-radius: 4px; margin-bottom: 10px; overflow: hidden; }
  .card-head { background: {{ $clr['primary'] }}; color: {{ $clr['accent'] }}; font-weight: bold; font-size: 11px; padding: 6px 12px; }
  .card-body { padding: 8px 12px; font-size: 11px; color: {{ $clr['valueText'] }}; background: {{ $clr['accentBg'] }}; }
  .card-row { margin-bottom: 4px; }
  .card-label { color: {{ $clr['primary'] }}; font-weight: bold; font-size: 10px; }

  /* ── Pills ── */
  .pill-active   { display: inline-block; background: {{ $clr['pillGreenBg'] }}; color: {{ $clr['pillGreenTx'] }}; font-size: 10px; padding: 3px 8px; border-radius: 12px; margin: 2px 3px 2px 0; border: 1px solid {{ $clr['pillGreenBd'] }}; }
  .pill-inactive { display: inline-block; background: {{ $clr['pillRedBg'] }};   color: {{ $clr['pillRedTx'] }};   font-size: 10px; padding: 3px 8px; border-radius: 12px; margin: 2px 3px 2px 0; border: 1px solid {{ $clr['pillRedBd'] }}; }
  .skill-pill    { display: inline-block; background: {{ $clr['skillBg'] }};      color: {{ $clr['primary'] }};     font-size: 10px; padding: 3px 8px; border-radius: 12px; margin: 2px 3px 2px 0; border: 1px solid {{ $clr['skillBd'] }}; }
  .source-label  { font-size: 9px; color: #888; margin-left: 4px; }

  /* ── Disclaimer ── */
  .disclaimer { background: {{ $clr['accentBg'] }}; border-left: 4px solid {{ $clr['accent'] }}; padding: 10px 14px; font-size: 10px; color: {{ $clr['valueText'] }}; line-height: 1.6; border-radius: 0 4px 4px 0; margin: 0 24px 18px; }
  .disclaimer-title { font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5px; color: {{ $clr['primary'] }}; border-bottom: 2px solid {{ $clr['accent'] }}; padding-bottom: 4px; margin-bottom: 8px; }
  .disclaimer ol { padding-left: 16px; }
  .disclaimer li { margin-bottom: 4px; }

  /* ── Common table key/value cells ── */
  .td-key { width: 42%; font-weight: bold; color: {{ $clr['primary'] }}; }
  .td-val { color: {{ $clr['valueText'] }}; }

  /* ── Yes/No badges ── */
  .badge-yes { display: inline-block; background: {{ $clr['pillGreenBg'] }}; color: {{ $clr['pillGreenTx'] }}; font-weight: bold; font-size: 10px; padding: 2px 8px; border-radius: 4px; border: 1px solid {{ $clr['pillGreenBd'] }}; }
  .badge-no  { display: inline-block; background: {{ $clr['pillRedBg'] }};   color: {{ $clr['pillRedTx'] }};   font-weight: bold; font-size: 10px; padding: 2px 8px; border-radius: 4px; border: 1px solid {{ $clr['pillRedBd'] }}; }

  /* ── Common section wrapper ── */
  .section { margin: 0 24px 18px; }

  /* ── No data message ── */
  .no-data { color: #888; font-style: italic; font-size: 11px; padding: 8px 0; }

  /* ── Footer ── */
  .rpt-footer { padding: 12px 24px; background: {{ $clr['primary'] }}; color: {{ $clr['metaText'] }}; font-size: 9px; }
  .rpt-footer-tbl { display: table; width: 100%; }
  .rpt-footer-left  { display: table-cell; vertical-align: middle; }
  .rpt-footer-right { display: table-cell; vertical-align: middle; text-align: right; }
  .rpt-footer-brand { color: {{ $clr['accent'] }}; font-weight: bold; font-size: 11px; }
</style>
