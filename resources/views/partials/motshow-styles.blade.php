<style>
.motshow-hero {
    position: relative;
    overflow: hidden;
    background: linear-gradient(160deg, #0f2167 0%, #1d4ed8 56%, #1e40af 100%);
    border-radius: 24px;
    padding: 1.15rem;
    margin-bottom: 1rem;
    box-shadow: 0 14px 36px rgba(15, 33, 103, .36);
}
.motshow-hero::before {
    content: '';
    position: absolute;
    top: -78px; right: -46px;
    width: 176px; height: 176px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
}
.motshow-hero::after {
    content: '';
    position: absolute;
    left: -22px; bottom: -62px;
    width: 138px; height: 138px;
    border-radius: 50%;
    background: rgba(255,255,255,.05);
}
.motshow-hero-inner { position: relative; z-index: 1; }
.motshow-chip {
    display: inline-flex; align-items: center; gap: .34rem;
    padding: .22rem .62rem; border-radius: 999px;
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.2);
    color: rgba(255,255,255,.88); font-size: .6rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .08em;
}
.motshow-avatar {
    width: 78px; height: 78px; border-radius: 22px; overflow: hidden;
    background: rgba(255,255,255,.18); border: 3px solid rgba(255,255,255,.24);
    color: #fff; display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 1.9rem; font-weight: 800;
    box-shadow: 0 10px 22px rgba(0,0,0,.18);
}
.motshow-avatar img { width: 100%; height: 100%; object-fit: cover; }
.motshow-name { margin-top: .65rem; font-size: 1.12rem; font-weight: 800; line-height: 1.2; color: #fff; }
.motshow-subtitle { margin-top: .2rem; color: rgba(255,255,255,.72); font-size: .73rem; line-height: 1.4; }
.motshow-meta-row { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .6rem; }
.motshow-meta-chip {
    display: inline-flex; align-items: center; gap: .24rem;
    padding: .18rem .48rem; border-radius: 999px;
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.12);
    color: rgba(255,255,255,.86); font-size: .62rem; font-weight: 700;
}
.motshow-status {
    display: inline-flex; align-items: center; gap: .25rem;
    padding: .16rem .55rem; border-radius: 999px;
    font-size: .6rem; font-weight: 800; letter-spacing: .05em;
    text-transform: uppercase; margin-top: .65rem;
}
.motshow-status--danger { background: rgba(239,68,68,.22); color: #fecaca; border: 1px solid rgba(252,165,165,.32); }
.motshow-status--warn   { background: rgba(251,191,36,.22); color: #fde68a; border: 1px solid rgba(252,211,77,.28); }
.motshow-status--info   { background: rgba(147,197,253,.18); color: #dbeafe; border: 1px solid rgba(147,197,253,.28); }
.motshow-status--safe   { background: rgba(74,222,128,.18); color: #bbf7d0; border: 1px solid rgba(74,222,128,.28); }
.motshow-stat-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: .55rem; margin-top: 1rem; }
.motshow-stat {
    text-align: center; padding: .78rem .42rem; border-radius: 15px;
    background: rgba(255,255,255,.11); border: 1px solid rgba(255,255,255,.16);
    backdrop-filter: blur(10px);
}
.motshow-stat-num  { font-size: 1.3rem; line-height: 1; font-weight: 800; color: #fff; }
.motshow-stat-label { margin-top: .22rem; font-size: .56rem; font-weight: 800; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: .08em; }
.motshow-alert {
    display: flex; align-items: flex-start; gap: .75rem;
    border-radius: 16px; padding: .9rem 1rem; margin-bottom: .95rem;
    border: 1px solid transparent;
}
.motshow-alert-icon {
    width: 42px; height: 42px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 1.05rem;
}
.motshow-alert--danger { background: linear-gradient(135deg,#fef2f2,#fff); border-color: #fecaca; }
.motshow-alert--danger .motshow-alert-icon { background: #fee2e2; color: #dc2626; }
.motshow-alert--amber  { background: linear-gradient(135deg,#fffbeb,#fff); border-color: #fde68a; }
.motshow-alert--amber .motshow-alert-icon  { background: #fef3c7; color: #d97706; }
.motshow-alert-title { font-size: .9rem; font-weight: 800; color: #0f172a; }
.motshow-alert-text  { margin-top: .16rem; font-size: .74rem; line-height: 1.45; color: #64748b; }
.motshow-action-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: .55rem; margin-bottom: 1rem; }
.motshow-action {
    min-height: 90px; border-radius: 18px; padding: .82rem .46rem;
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; gap: .34rem; text-align: center;
    text-decoration: none; border: 1px solid transparent;
    font-size: .72rem; font-weight: 800;
}
.motshow-action i { font-size: 1.32rem; }
.motshow-action--danger { background: linear-gradient(135deg,#dc2626,#b91c1c); color: #fff; box-shadow: 0 8px 18px rgba(220,38,38,.24); }
.motshow-action--blue   { background: linear-gradient(135deg,#1d4ed8,#1e40af); color: #fff; box-shadow: 0 8px 18px rgba(29,78,216,.24); }
.motshow-action--ghost  { background: #fff; color: #334155; border-color: #e2e8f0; }
.motshow-section {
    display: flex; align-items: center; gap: .5rem; margin-bottom: .65rem;
    font-size: .6rem; font-weight: 800; color: #94a3b8;
    text-transform: uppercase; letter-spacing: .1em;
}
.motshow-section::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }
.motshow-card {
    background: #fff; border-radius: 18px;
    border: 1px solid rgba(15,23,42,.05);
    box-shadow: 0 3px 16px rgba(15,23,42,.06);
    overflow: hidden; margin-bottom: .9rem;
}
.motshow-card-head { padding: .88rem 1rem .32rem; font-size: .64rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: .08em; }
.motshow-card-body { padding: 0 1rem 1rem; }
.motshow-feature-box { background: linear-gradient(135deg,#eff6ff,#f8fbff); border: 1px solid #dbeafe; border-radius: 16px; padding: .9rem; }
.motshow-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .9rem .8rem; }
.motshow-info-full { grid-column: 1 / -1; }
.motshow-label { margin-bottom: .18rem; font-size: .63rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: .08em; }
.motshow-value { font-size: .88rem; line-height: 1.38; font-weight: 700; color: #0f172a; }
.motshow-value--soft { font-weight: 600; color: #334155; }
.motshow-license-code { display: inline-block; margin: .08rem .16rem 0 0; padding: .09rem .38rem; border-radius: 8px; background: #fef3c7; color: #92400e; font-size: .68rem; font-weight: 800; }
.motshow-license-flag { display: inline-flex; align-items: center; gap: .25rem; margin-top: .36rem; padding: .12rem .44rem; border-radius: 999px; font-size: .58rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; }
.motshow-license-flag--danger { background: #fef2f2; color: #b91c1c; border: 1px solid #fca5a5; }
.motshow-license-flag--safe   { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }
.motshow-photo-main { width: 100%; max-height: 280px; object-fit: contain; border-radius: 14px; box-shadow: 0 4px 16px rgba(15,23,42,.1); }
.motshow-list { display: flex; flex-direction: column; gap: .7rem; }
.motshow-item {
    display: flex; align-items: flex-start; gap: .85rem; padding: .92rem;
    background: #fff; border: 1px solid rgba(15,23,42,.05); border-radius: 16px;
    color: inherit; text-decoration: none; box-shadow: 0 2px 10px rgba(15,23,42,.04);
}
.motshow-item--static { cursor: default; }
.motshow-item-icon { width: 42px; height: 42px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #fff; box-shadow: 0 6px 16px rgba(15,23,42,.12); }
.motshow-item-icon--danger { background: linear-gradient(135deg,#ef4444,#dc2626); }
.motshow-item-icon--blue   { background: linear-gradient(135deg,#2563eb,#1d4ed8); }
.motshow-item-icon--slate  { background: linear-gradient(135deg,#64748b,#475569); }
.motshow-item-title   { font-size: .88rem; line-height: 1.3; font-weight: 800; color: #0f172a; }
.motshow-item-meta    { margin-top: .14rem; font-size: .71rem; line-height: 1.46; color: #64748b; }
.motshow-item-submeta { margin-top: .2rem; font-size: .67rem; line-height: 1.45; color: #94a3b8; }
.motshow-tag { display: inline-flex; align-items: center; gap: .22rem; padding: .12rem .44rem; border-radius: 8px; font-size: .63rem; font-weight: 800; }
.motshow-tag--plate { background: #eff6ff; color: #1e40af; }
.motshow-tag--owner { background: #fef9c3; color: #92400e; }
.motshow-inline-photos { display: flex; gap: .35rem; margin-top: .48rem; padding-bottom: .1rem; overflow-x: auto; }
.motshow-inline-photos img { width: 58px; height: 42px; object-fit: cover; border-radius: 8px; border: 1.5px solid #e2e8f0; flex-shrink: 0; }
</style>
