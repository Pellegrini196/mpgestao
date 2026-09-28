const {readFileSync} = require('node:fs');
const {runInNewContext} = require('node:vm');
const assert = require('node:assert/strict');
const source = readFileSync('public/assets/theme.js', 'utf8');
function setup(saved, dark = false, blocked = false) {
  const events = {}, systemEvents = {}, windowEvents = {}, data = {}, writes = [];
  const controls = [{hidden: true}];
  const buttons = ['light','dark'].map(value => ({dataset:{themeChoice:value}, attributes:{}, handlers:{}, setAttribute(k,v){this.attributes[k]=v}, addEventListener(k,v){this.handlers[k]=v}}));
  const system = {matches:dark, addEventListener(k,v){systemEvents[k]=v}};
  runInNewContext(source, {
    document:{documentElement:{dataset:data}, querySelectorAll:s=>s === '.theme-switch' ? controls : buttons, addEventListener(k,v){events[k]=v}},
    window:{matchMedia:()=>system, addEventListener(k,v){windowEvents[k]=v}},
    localStorage:{getItem(){if(blocked)throw new Error('blocked');return saved}, setItem(k,v){if(blocked)throw new Error('blocked');writes.push([k,v])}}
  });
  assert.equal(data.theme, saved === 'dark' || saved === 'light' ? saved : dark ? 'dark':'light');
  events.DOMContentLoaded();
  assert.equal(controls[0].hidden,false);
  return {data,buttons,writes,system,systemEvents,windowEvents};
}
for(const dark of [false,true]) {
  const app=setup(null,dark);
  app.system.matches=!dark;app.systemEvents.change();
  assert.equal(app.data.theme, dark?'light':'dark');
}
for(const theme of ['light','dark']) {
  const app=setup(theme,theme!=='dark');
  const opposite=theme==='light'?'dark':'light';
  app.buttons.find(b=>b.dataset.themeChoice===opposite).handlers.click();
  assert.equal(app.data.theme,opposite);
  assert.equal(app.data.bsTheme,opposite);
  assert.deepEqual(app.writes,[['mpgestao.theme',opposite]]);
  assert.equal(app.buttons.filter(b=>b.attributes['aria-pressed']==='true').length,1);
  app.systemEvents.change();assert.equal(app.data.theme,opposite);
  setup(app.writes[0][1]); // Preferência reaplicada na próxima página.
  app.windowEvents.storage({key:'mpgestao.theme',newValue:theme});
  assert.equal(app.data.theme,theme);
  app.windowEvents.storage({key:'mpgestao.theme',newValue:null});
  assert.equal(app.data.theme,app.system.matches?'dark':'light');
}
setup('invalid',true);
const blocked=setup(null,false,true);
blocked.buttons[1].handlers.click();assert.equal(blocked.data.theme,'dark');
console.log('OK: temas, sistema, persistência, sincronização, acessibilidade e armazenamento bloqueado.');
