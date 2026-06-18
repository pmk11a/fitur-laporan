const { chromium } = require('./fe-fitur/node_modules/playwright');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  const errors = [];
  page.on('pageerror', (err) => errors.push('PAGEERROR: ' + err.message));
  page.on('console', (msg) => {
    if (msg.type() === 'error') errors.push('CONSOLE.ERROR: ' + msg.text());
  });

  console.log('--- Loading /reports/020102 ---');
  await page.goto('http://localhost:3000/reports/020102', { waitUntil: 'networkidle', timeout: 30000 });
  await page.waitForTimeout(2000);

  const title = await page.title();
  console.log('Page title:', title);

  // Check for ReferenceError or saldoAwal errors
  const refErrors = errors.filter(e => /saldoAwal|ReferenceError/i.test(e));
  if (refErrors.length > 0) {
    console.log('\n!!! BUG STILL PRESENT:');
    refErrors.forEach(e => console.log('  ' + e));
  } else {
    console.log('\nNo saldoAwal/ReferenceError detected.');
  }

  if (errors.length > 0) {
    console.log('\nAll page errors:');
    errors.forEach(e => console.log('  ' + e));
  } else {
    console.log('No page errors at all.');
  }

  // Check if form is present
  const formExists = await page.locator('form, [data-testid="filter-form"]').count();
  console.log('\nForm elements found:', formExists);

  // Try to fill required date filters and submit
  console.log('\n--- Trying to fill filters and submit ---');
  const dateInputs = await page.locator('input[type="date"]').count();
  console.log('Date inputs:', dateInputs);

  if (dateInputs >= 2) {
    await page.locator('input[type="date"]').nth(0).fill('2024-01-01');
    await page.locator('input[type="date"]').nth(1).fill('2024-12-31');
    await page.waitForTimeout(500);

    // Find submit button
    const submitBtn = page.locator('button:has-text("Tampilkan"), button:has-text("Submit"), button:has-text("Lihat"), button[type="submit"]').first();
    if (await submitBtn.count() > 0) {
      await submitBtn.click();
      console.log('Clicked submit');
      await page.waitForTimeout(3000);
    }
  }

  // Take screenshot
  await page.screenshot({ path: 'tmp_screenshot.png', fullPage: true });
  console.log('Screenshot saved to tmp_screenshot.png');

  // Check for saldoAwal / Tunai in DOM
  const tunaiCount = await page.locator('text=/Tunai/i').count();
  const saldoCount = await page.locator('text=/Saldo/i').count();
  console.log('\nElements with "Tunai":', tunaiCount);
  console.log('Elements with "Saldo":', saldoCount);

  if (errors.length > 0) {
    console.log('\nFinal errors:');
    errors.forEach(e => console.log('  ' + e));
  }

  await browser.close();
  process.exit(errors.length > 0 ? 1 : 0);
})();
