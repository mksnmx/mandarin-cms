define(['jquery'], function ($) {
  'use strict'

  var LINK_FIELD_NAME = 'Ссылка на оплату'
  var DEFAULT_DESCRIPTION = 'Оплата услуг по договору'
  var DEBOUNCE_MS = 500
  var LB_BASE_URL = 'https://lending-billing.mndr.in'
  var EPF_BASE_URL = 'https://secure-app.mandarin.io'
  var PAYMENT_URL = 'https://secure-app.mandarin.io/payment/'
  var TERM_KEY = 'transaction_info.terminal_info.term'
  var FEES_TTL_MS = 10 * 60 * 1000
  var TIMEOUT_MS = 15000
  var METHOD_ORDER = ['credit', 'rus_card', 'bnpl']
  var METHOD_LABELS = { credit: 'Рассрочка', rus_card: 'Карта', bnpl: 'Дольки' }

  var LOGO =
    '<svg class="mnd-logo" viewBox="0 0 295 42" role="img" aria-label="Mandarin" fill="currentColor">' +
    '<path d="M38.255,1.507 L32.717,1.507 C31.598,1.507 30.871,2.069 30.591,3.195 L23.375,26.781 C22.816,28.639 22.424,30.327 21.921,32.128 L20.914,32.128 C20.41,30.327 20.019,28.639 19.459,26.894 L12.243,3.195 C11.908,2.069 11.292,1.507 10.174,1.507 L4.58,1.507 C3.293,1.507 2.566,2.238 2.51,3.59 L1,40.347 L5.922,40.347 L7.265,5.616 L8.384,5.616 L16.998,34.267 C17.334,35.337 18.061,35.956 19.236,35.956 L23.655,35.956 C24.774,35.956 25.501,35.337 25.781,34.267 L34.395,5.616 L35.57,5.616 L36.968,40.347 L41.891,40.347 L40.269,3.59 C40.269,2.238 39.541,1.507 38.255,1.507"/>' +
    '<path d="M59.294,23.91 L64.161,8.486 L65.113,5.335 L66.678,5.335 C66.958,6.348 67.293,7.474 67.629,8.486 L72.496,23.91 L59.294,23.91 z M68.468,1.507 L63.323,1.507 C62.427,1.507 61.7,2.014 61.476,2.857 L49.058,40.347 L54.148,40.347 L57.952,28.188 L73.838,28.188 L77.643,40.347 L82.733,40.347 L70.315,2.857 C70.091,2.014 69.364,1.507 68.468,1.507"/>' +
    '<path d="M115.518,29.651 C115.518,31.622 115.575,33.535 115.686,35.562 L114.735,35.562 C113.896,33.648 112.889,31.509 112.106,29.933 L99.464,3.083 C99.016,2.014 98.401,1.507 97.283,1.507 L92.639,1.507 C91.353,1.507 90.681,2.183 90.681,3.476 L90.681,40.347 L95.604,40.347 L95.604,11.245 C95.604,9.557 95.548,7.812 95.38,6.122 L96.276,6.122 C96.723,7.698 97.785,9.781 98.457,11.245 L111.491,38.77 C111.938,39.84 112.553,40.347 113.672,40.347 L118.483,40.347 C119.713,40.347 120.385,39.671 120.385,38.377 L120.385,1.507 L115.518,1.507 L115.518,29.651"/>' +
    '<path d="M146.402,36.518 C143.045,36.518 140.08,36.463 137.563,36.406 L137.563,5.391 C140.192,5.335 142.989,5.278 146.402,5.278 C153.785,5.278 156.302,9.162 156.302,20.927 C156.302,32.691 153.785,36.518 146.402,36.518 z M146.402,1 C141.087,1 136.668,1.225 132.64,1.507 L132.64,40.347 C136.668,40.628 141.087,40.854 146.402,40.854 C157.477,40.854 161.449,35.956 161.449,20.927 C161.449,5.897 157.477,1 146.402,1"/>' +
    '<path d="M178.011,23.91 L182.878,8.486 L183.829,5.335 L185.395,5.335 C185.675,6.348 186.01,7.474 186.346,8.486 L191.213,23.91 L178.011,23.91 z M187.185,1.507 L182.039,1.507 C181.144,1.507 180.416,2.014 180.193,2.857 L167.775,40.347 L172.865,40.347 L176.669,28.188 L192.555,28.188 L196.36,40.347 L201.45,40.347 L189.032,2.857 C188.808,2.014 188.08,1.507 187.185,1.507"/>' +
    '<path d="M222.599,19.857 L214.264,19.857 L214.264,5.335 C216.446,5.278 219.298,5.278 222.599,5.278 C228.305,5.278 230.206,6.798 230.206,12.483 C230.206,18.225 228.305,19.801 222.599,19.857 z M228.08,22.503 L228.08,22.222 C233.171,21.546 235.185,18.506 235.185,12.202 C235.185,3.476 231.66,1 222.71,1 C216.501,1 213.593,1.057 209.397,1.507 L209.397,40.347 L214.264,40.347 L214.264,24.079 C216.39,24.079 219.187,24.136 222.767,24.136 C225.34,24.136 226.907,25.261 227.913,28.188 L231.325,40.347 L236.583,40.347 L232.835,28.075 C231.996,24.811 229.983,23.01 228.08,22.503"/>' +
    '<path d="M246.489,1.507 L251.411,1.507 L251.411,40.347 L246.489,40.347 z"/>' +
    '<path d="M288.501,1.507 L288.501,29.651 C288.501,31.622 288.558,33.535 288.669,35.562 L287.718,35.562 C286.879,33.648 285.872,31.509 285.089,29.933 L272.448,3.083 C271.999,2.014 271.384,1.507 270.266,1.507 L265.622,1.507 C264.336,1.507 263.664,2.183 263.664,3.476 L263.664,40.347 L268.587,40.347 L268.587,11.245 C268.587,9.557 268.532,7.812 268.364,6.122 L269.259,6.122 C269.706,7.698 270.769,9.781 271.441,11.245 L284.474,38.77 C284.921,39.84 285.536,40.347 286.656,40.347 L291.466,40.347 C292.697,40.347 293.368,39.671 293.368,38.377 L293.368,1.507 L288.501,1.507"/>' +
    '</svg>'

  var MODES = {
    cart_amount: { tab: 'По сумме заявки', label: 'Сумма заявки', hint: 'цена услуги для клиента' },
    settlement_amount: { tab: 'По сумме к получению', label: 'Сумма к получению', hint: 'сколько получит компания' },
  }

  // ---------- утилиты

  function money(n) {
    var value = Number(n)
    var whole = Math.round(value * 100) % 100 === 0
    return value.toLocaleString('ru-RU', {
      minimumFractionDigits: whole ? 0 : 2,
      maximumFractionDigits: 2,
    }) + '\u00a0₽'
  }

  function parseAmount(text) {
    var clean = String(text || '').replace(/[\s\u00a0₽]/g, '').replace(',', '.')
    if (!/^\d+(\.\d{0,2})?$/.test(clean)) return null
    return Number(clean)
  }

  function normalizePhone(value) {
    var digits = String(value || '').replace(/\D/g, '')
    if (digits.length === 10 && digits.charAt(0) === '9') digits = '7' + digits
    if (digits.length === 11 && digits.charAt(0) === '8') digits = '7' + digits.slice(1)
    return digits.length === 11 && digits.charAt(0) === '7' ? digits : null
  }

  function prettyPhone(digits) {
    return '+7 ' + digits.slice(1, 4) + ' ' + digits.slice(4, 7) + '-' + digits.slice(7, 9) + '-' + digits.slice(9)
  }

  function esc(text) {
    return String(text == null ? '' : text).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
    })
  }

  function node(html) {
    var t = document.createElement('template')
    t.innerHTML = html.trim()
    return t.content.firstElementChild
  }

  function copyText(text) {
    if (navigator.clipboard && window.isSecureContext) return navigator.clipboard.writeText(text)
    var area = document.createElement('textarea')
    area.value = text
    area.style.position = 'fixed'
    area.style.opacity = '0'
    document.body.appendChild(area)
    area.select()
    document.execCommand('copy')
    area.remove()
    return Promise.resolve()
  }

  function errorText(err) {
    var json = err && (err.responseJSON || err.json)
    if (json && json.error && json.error.message) return json.error.message
    if (err && err.status === 0) return 'Сервис Mandarin недоступен. Проверьте интернет и попробуйте ещё раз'
    if (err && err.message) return err.message
    return 'Что-то пошло не так. Попробуйте ещё раз'
  }

  // Сроки, которые увидит клиент на платёжной странице (SPEC, раздел 4)
  function invoiceTerms(amountType, onlySelected, selected, allTerms) {
    if (amountType === 'settlement_amount' || onlySelected) return [selected]
    return allTerms.filter(function (t) { return t <= selected })
  }

  // ---------- Mandarin: тарифы, расчёт, счёт (прямо из браузера, Bearer мерчанта)

  function parseMethodsList(raw, fallback) {
    var source = Array.isArray(raw) ? raw : fallback
    var out = []
    ;(source || []).forEach(function (item) {
      var key = String(item || '')
      if (METHOD_ORDER.indexOf(key) >= 0 && out.indexOf(key) < 0) out.push(key)
    })
    return METHOD_ORDER.filter(function (m) { return out.indexOf(m) >= 0 })
  }

  function parseConfig(bearer, raw) {
    if (!String(bearer || '').trim()) throw new Error('Виджет не настроен: не указан токен Mandarin')
    var config
    try {
      config = typeof raw === 'string' ? JSON.parse(raw) : raw
    } catch (e) {
      throw new Error('Виджет не настроен: конфигурация — невалидный JSON')
    }
    if (!config || !config.clientId) throw new Error('Виджет не настроен: в конфигурации нет clientId')
    if (!Array.isArray(config.tariffs) || !config.tariffs.length) throw new Error('Виджет не настроен: в конфигурации нет тарифов')
    config.tariffs.forEach(function (t) {
      if (!t.key || !t.label || !t.merchantId || !t.paymentOptionsId) {
        throw new Error('Виджет не настроен: у тарифа нужны key, label, merchantId, paymentOptionsId')
      }
    })
    var methods = parseMethodsList(config.methods, ['credit'])
    if (!methods.length) throw new Error('Виджет не настроен: в methods нет ни одного способа (credit, rus_card, bnpl)')
    var methodsDefault = parseMethodsList(config.methodsDefault, methods).filter(function (m) {
      return methods.indexOf(m) >= 0
    })
    if (!methodsDefault.length) methodsDefault = methods.slice()
    var limits = config.limits || {}
    return {
      bearer: String(bearer).trim(),
      clientId: String(config.clientId),
      methods: methods,
      methodsDefault: methodsDefault,
      tariffs: config.tariffs.map(function (t) {
        return { key: String(t.key), label: String(t.label), merchantId: String(t.merchantId), paymentOptionsId: String(t.paymentOptionsId) }
      }),
      limits: { min: Number(limits.min) || 1, max: Number(limits.max) || 10000000 },
      vat: config.vat || 'None',
      fiscalReceipt: config.fiscalReceipt !== false,
      defaultDescription: config.defaultDescription || DEFAULT_DESCRIPTION,
    }
  }

  function parseMerchantFees(json) {
    var list = (json && (json.merchantFees || json.merchant_fees)) || []
    return list.map(function (fee) {
      var model = fee.pricingModel || fee.pricing_model || {}
      var items = model.pricingModelItems || model.pricing_model_items || []
      var terms = []
      items.forEach(function (item) {
        if ((item.channelId || item.channel_id) !== '*') return
        ;(item.transactionConditions || item.transaction_conditions || []).forEach(function (c) {
          var term = c.key === TERM_KEY ? parseInt(c.value, 10) : 0
          if (term > 0 && terms.indexOf(term) < 0) terms.push(term)
        })
      })
      return {
        merchantId: String(fee.merchantId != null ? fee.merchantId : fee.merchant_id),
        pricingModelName: model.name,
        terms: terms.sort(function (a, b) { return a - b }),
      }
    })
  }

  function normalizeCalculations(json, amountType, amount) {
    var input = round2(amount)
    var byCart = amountType === 'cart_amount'
    return ((json && json.calculations) || [])
      .filter(function (c) { return (c.channel_id || c.channelId || '*') === '*' })
      .map(function (c) {
        var a = c.calculation_amount || c.calculationAmount || {}
        var counterpart = round2(Math.abs(Number(a.amount)))
        return {
          term: parseInt(c.term, 10),
          invoiceAmount: byCart ? input : counterpart,
          settlementAmount: byCart ? counterpart : input,
          monthly: round2(a.recurring_payment != null ? a.recurring_payment : a.recurringPayment),
          overpayment: round2(a.customer_overpayment_amount != null ? a.customer_overpayment_amount : a.customerOverpaymentAmount),
          clientTotal: round2(a.customer_total_payments != null ? a.customer_total_payments : a.customerTotalPayments),
        }
      })
      .sort(function (x, y) { return x.term - y.term })
  }

  function makeOrderId(leadId, date) {
    return 'amo-' + leadId + '-' + date.toISOString().replace(/\D/g, '').slice(0, 14)
  }

  function buildInvoiceBody(p) {
    var total = round2(p.invoiceAmount)
    var order = { id: makeOrderId(p.leadId, p.date) }
    if (p.email) order.email = p.email
    if (p.phone) order.phone = p.phone
    var types = (p.paymentMethodTypes || []).slice()
    var body = {
      payment_options_id: p.tariff.paymentOptionsId,
      order: order,
      cart: {
        fiscal_receipt_is_required: p.fiscalReceipt,
        total_price: total,
        items: [{
          quantity: 1,
          price: total,
          total_price: total,
          vat: p.vat,
          description: p.description,
          calculation_method: 'PREPAY_FULL',
          payment_subject: 'SERVICE',
        }],
      },
      payment_method_types: types,
    }
    if (types.indexOf('credit') >= 0) {
      body.payment_method_options = { credit: { terms: (p.terms || []).map(String) } }
    }
    return body
  }

  function upstreamMessage(json, text, status) {
    var candidate = json && (json.message || json.detail || (json.error && json.error.message) || json.error || json.errors)
    if (typeof candidate === 'string' && candidate) return candidate
    if (candidate) return JSON.stringify(candidate).slice(0, 300)
    if (status === 401 || status === 403) return 'нет доступа — проверьте токен Mandarin в настройках виджета'
    if (text && text.length < 300 && text.charAt(0) !== '<') return text
    return 'ошибка ' + status
  }

  function round2(n) {
    return Math.round(Number(n) * 100) / 100
  }

  // Тот же контракт, что был у прокси: call({action: 'tariffs' | 'calculate' | 'create_invoice', ...})
  function createMandarinApi(options) {
    var config = options.config
    var fetchImpl = options.fetch || function (url, init) { return fetch(url, init) }
    var lbBase = options.lbBaseUrl || LB_BASE_URL
    var epfBase = options.epfBaseUrl || EPF_BASE_URL
    var now = options.now || Date.now
    var feesCache = null

    function request(url, init, label) {
      var controller = typeof AbortController === 'function' ? new AbortController() : null
      var timer = controller ? setTimeout(function () { controller.abort() }, TIMEOUT_MS) : null
      var headers = Object.assign({ Authorization: 'Bearer ' + config.bearer, Accept: 'application/json' }, init.headers)
      return fetchImpl(url, Object.assign({}, init, { headers: headers, signal: controller ? controller.signal : undefined }))
        .then(
          function (res) {
            return res.text().then(function (text) {
              var json = null
              try { json = text ? JSON.parse(text) : null } catch (e) { json = null }
              if (!res.ok) throw new Error(label + ': ' + upstreamMessage(json, text, res.status))
              return json || {}
            })
          },
          function (e) {
            if (e && e.name === 'AbortError') throw new Error(label + ': Mandarin не ответил за ' + TIMEOUT_MS / 1000 + ' с')
            throw new Error(label + ': нет связи с Mandarin')
          }
        )
        .then(
          function (json) { clearTimeout(timer); return json },
          function (e) { clearTimeout(timer); throw e }
        )
    }

    function getFees() {
      if (feesCache && feesCache.expiresAt > now()) return Promise.resolve(feesCache.value)
      return request(lbBase + '/api/merchant-fees/' + encodeURIComponent(config.clientId) + '/actual', { method: 'GET' }, 'Тарифы')
        .then(function (json) {
          feesCache = { value: parseMerchantFees(json), expiresAt: now() + FEES_TTL_MS }
          return feesCache.value
        })
    }

    function resolveTariffs() {
      if (config.methods.indexOf('credit') < 0) {
        return Promise.resolve(config.tariffs.map(function (t) {
          return Object.assign({}, t, { pricingModelName: '', terms: [] })
        }))
      }
      return getFees().then(function (fees) {
        return config.tariffs.map(function (t) {
          var fee = fees.filter(function (f) { return f.merchantId === t.merchantId })[0]
          if (!fee) throw new Error('Тариф «' + t.label + '»: магазин ' + t.merchantId + ' не найден в Mandarin')
          return Object.assign({}, t, { pricingModelName: fee.pricingModelName, terms: fee.terms })
        })
      })
    }

    function resolveInvoiceMethods(selected) {
      var list = parseMethodsList(selected, config.methodsDefault).filter(function (m) {
        return config.methods.indexOf(m) >= 0
      })
      return METHOD_ORDER.filter(function (m) { return list.indexOf(m) >= 0 })
    }

    function findTariff(key) {
      return resolveTariffs().then(function (list) {
        var tariff = list.filter(function (t) { return t.key === key })[0]
        if (!tariff) throw new Error('Неизвестный тариф')
        return tariff
      })
    }

    function validAmount(value) {
      var amount = round2(value)
      if (!isFinite(amount) || amount < config.limits.min || amount > config.limits.max) {
        throw new Error('Сумма должна быть от ' + money(config.limits.min) + ' до ' + money(config.limits.max))
      }
      return amount
    }

    function validTerms(terms, allowed) {
      if (!Array.isArray(terms) || !terms.length) throw new Error('Не выбран срок рассрочки')
      var parsed = terms.map(function (t) { return parseInt(t, 10) })
      var unknown = parsed.filter(function (t) { return allowed.indexOf(t) < 0 })
      if (unknown.length) throw new Error('Срок ' + unknown.join(', ') + ' мес. недоступен в тарифе')
      return parsed.filter(function (t, i) { return parsed.indexOf(t) === i }).sort(function (a, b) { return a - b })
    }

    function validEmail(value) {
      var email = String(value || '').trim()
      if (!email) return ''
      if (email.length > 254 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) throw new Error('Email клиента указан неверно')
      return email
    }

    function validPhone(value) {
      if (!String(value || '').trim()) return ''
      var phone = normalizePhone(value)
      if (!phone) throw new Error('Телефон клиента: нужен российский номер, например +7 999 123-45-67')
      return phone
    }

    var actions = {
      tariffs: function () {
        return resolveTariffs().then(function (list) {
          return {
            tariffs: list.map(function (t) { return { key: t.key, label: t.label, pricingModelName: t.pricingModelName, terms: t.terms } }),
            limits: config.limits,
            methods: config.methods.slice(),
            methodsDefault: config.methodsDefault.slice(),
          }
        })
      },
      calculate: function (p) {
        if (config.methods.indexOf('credit') < 0) return Promise.reject(new Error('Рассрочка недоступна в конфигурации'))
        if (!MODES[p.amountType]) return Promise.reject(new Error('Неизвестный режим расчёта'))
        return findTariff(p.tariff).then(function (tariff) {
          var amount = validAmount(p.amount)
          var body = { pricing_model_name: tariff.pricingModelName }
          body[p.amountType] = amount
          return request(lbBase + '/api/calculator/preview-amounts', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
          }, 'Расчёт').then(function (json) {
            return { rows: normalizeCalculations(json, p.amountType, amount) }
          })
        })
      },
      create_invoice: function (p) {
        var methods = resolveInvoiceMethods(p.paymentMethodTypes)
        if (!methods.length) throw new Error('Выберите хотя бы один способ оплаты')
        var leadId = Number(p.leadId)
        if (!(leadId > 0) || Math.floor(leadId) !== leadId) throw new Error('Не удалось определить сделку')
        var description = String(p.description || '').trim() || config.defaultDescription
        var invoiceAmount = validAmount(p.invoiceAmount)
        var email = validEmail(p.email)
        var phone = validPhone(p.phone)
        var useCredit = methods.indexOf('credit') >= 0

        function send(tariff, terms) {
          var body = buildInvoiceBody({
            tariff: tariff,
            leadId: leadId,
            invoiceAmount: invoiceAmount,
            terms: terms,
            paymentMethodTypes: methods,
            description: description.slice(0, 128),
            vat: config.vat,
            fiscalReceipt: config.fiscalReceipt,
            date: new Date(now()),
            email: email,
            phone: phone,
          })
          return request(epfBase + '/api/v1/public/invoices/', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
          }, 'Счёт').then(function (json) {
            if (!json.paymentId) throw new Error('Счёт: ' + upstreamMessage(json, '', 200))
            return { paymentId: json.paymentId, url: PAYMENT_URL + json.paymentId, orderId: body.order.id }
          })
        }

        if (useCredit) {
          return findTariff(p.tariff || config.tariffs[0].key).then(function (tariff) {
            return send(tariff, validTerms(p.terms, tariff.terms))
          })
        }
        var key = p.tariff || config.tariffs[0].key
        var tariff = config.tariffs.filter(function (t) { return t.key === key })[0] || config.tariffs[0]
        return send(tariff, [])
      },
    }

    return function call(payload) {
      var run = payload && Object.prototype.hasOwnProperty.call(actions, payload.action) ? actions[payload.action] : null
      if (!run) return Promise.reject(new Error('Неизвестное действие'))
      try {
        return Promise.resolve(run(payload))
      } catch (e) {
        return Promise.reject(e)
      }
    }
  }

  // ---------- API amoCRM (сессия менеджера, тот же домен)

  function createAmoApi() {
    function request(method, path, body) {
      return fetch(path, {
        method: method,
        credentials: 'include',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: body ? JSON.stringify(body) : undefined,
      }).then(function (res) {
        if (res.status === 204) return null
        if (!res.ok) throw new Error('amoCRM ответил ' + res.status)
        return res.json()
      })
    }

    function firstValue(entity, code) {
      var fields = (entity && entity.custom_fields_values) || []
      for (var i = 0; i < fields.length; i++) {
        if (fields[i].field_code === code && fields[i].values && fields[i].values.length) return fields[i].values[0].value
      }
      return ''
    }

    return {
      getClient: function (leadId) {
        return request('GET', '/api/v4/leads/' + leadId + '?with=contacts').then(function (lead) {
          var contacts = (lead && lead._embedded && lead._embedded.contacts) || []
          var main = contacts.filter(function (c) { return c.is_main })[0] || contacts[0]
          var base = { leadName: (lead && lead.name) || '', phone: '', email: '' }
          if (!main) return base
          return request('GET', '/api/v4/contacts/' + main.id).then(function (contact) {
            base.phone = firstValue(contact, 'PHONE')
            base.email = firstValue(contact, 'EMAIL')
            return base
          })
        })
      },
      findLeadField: function (name) {
        function page(n) {
          return request('GET', '/api/v4/leads/custom_fields?limit=250&page=' + n).then(function (json) {
            var fields = (json && json._embedded && json._embedded.custom_fields) || []
            var found = fields.filter(function (f) { return f.name.trim().toLowerCase() === name.toLowerCase() })[0]
            if (found) return found.id
            return json && json._links && json._links.next ? page(n + 1) : null
          })
        }
        return page(1)
      },
      setLeadField: function (leadId, fieldId, value) {
        return request('PATCH', '/api/v4/leads/' + leadId, {
          custom_fields_values: [{ field_id: fieldId, values: [{ value: value }] }],
        })
      },
      addNote: function (leadId, text) {
        return request('POST', '/api/v4/leads/' + leadId + '/notes', [{ note_type: 'common', params: { text: text } }])
      },
    }
  }

  function saveToLead(amo, leadId, linkFieldId, url, note) {
    var problems = []
    var field = Promise.resolve(linkFieldId || amo.findLeadField(LINK_FIELD_NAME))
      .then(function (id) {
        if (!id) {
          problems.push('нет поля «' + LINK_FIELD_NAME + '»')
          return null
        }
        return amo.setLeadField(leadId, id, url)
      })
      .catch(function () { problems.push('не записалось поле сделки') })
    var feed = amo.addNote(leadId, note).catch(function () { problems.push('не добавилось примечание') })
    return Promise.all([field, feed]).then(function () { return problems })
  }

  // ---------- разметка

  function panelHtml() {
    return (
      '<div class="mnd-root mnd-panel">' +
      '<div class="mnd-panel__brand">' + LOGO + '</div>' +
      '<div class="mnd-panel__text">Создайте ссылку на&nbsp;оплату для&nbsp;клиента</div>' +
      '<button type="button" class="mnd-panel__btn mnd-open">Оплата Mandarin</button>' +
      '</div>'
    )
  }

  function modalHtml() {
    return (
      '<div class="mnd-root mnd-overlay">' +
      '<div class="mnd-modal" role="dialog" aria-modal="true" aria-label="Оплата Mandarin">' +
      '<header class="mnd-head">' +
      '<div class="mnd-head__top">' + LOGO + '<button type="button" class="mnd-close" aria-label="Закрыть">×</button></div>' +
      '<div class="mnd-head__title">Оплата Mandarin</div>' +
      '<div class="mnd-head__sub">Ссылка на оплату: рассрочка, карта или Дольки</div>' +
      '</header>' +
      '<div class="mnd-body" data-role="body"></div>' +
      '</div></div>'
    )
  }

  function formHtml() {
    return (
      '<div class="mnd-form">' +
      '<div class="mnd-label">Способы оплаты</div>' +
      '<div class="mnd-methods" data-role="methods"></div>' +
      '<div data-role="credit-block">' +
      '<div class="mnd-seg mnd-seg--tariffs" data-role="tariffs"></div>' +
      '<div class="mnd-seg" data-role="modes">' +
      '<button type="button" data-mode="cart_amount">' + MODES.cart_amount.tab + '</button>' +
      '<button type="button" data-mode="settlement_amount">' + MODES.settlement_amount.tab + '</button>' +
      '</div>' +
      '</div>' +
      '<div class="mnd-label"><span data-role="amount-label"></span><span class="mnd-pill" data-role="amount-hint"></span></div>' +
      '<label class="mnd-input"><input data-role="amount" inputmode="decimal" autocomplete="off" placeholder="Например, 150 000"><span>₽</span></label>' +
      '<div class="mnd-hint" data-role="limits"></div>' +
      '<div data-role="credit-calc">' +
      '<div class="mnd-label">Срок рассрочки, мес.</div>' +
      '<div class="mnd-terms" data-role="terms"></div>' +
      '<div class="mnd-msg mnd-msg--error" data-role="calc-error" hidden></div>' +
      '<div class="mnd-details" data-role="details"></div>' +
      '<div class="mnd-offer" data-role="offer">' +
      '<label class="mnd-check" data-role="only-wrap"><input type="checkbox" data-role="only"><span>Только выбранный срок</span></label>' +
      '<div class="mnd-hint" data-role="terms-hint"></div>' +
      '</div>' +
      '</div>' +
      '<details class="mnd-more">' +
      '<summary><span>Клиент и назначение платежа</span><small data-role="client-summary"></small></summary>' +
      '<div class="mnd-more__body">' +
      '<label class="mnd-field"><span>Телефон</span><input data-role="phone" inputmode="tel" placeholder="+7 999 123-45-67"></label>' +
      '<label class="mnd-field"><span>Email</span><input data-role="email" inputmode="email" placeholder="client@mail.ru"></label>' +
      '<label class="mnd-field"><span>Назначение платежа</span><input data-role="description" maxlength="128" placeholder="' + esc(DEFAULT_DESCRIPTION) + '"></label>' +
      '<div class="mnd-hint">Телефон и email — из контакта. Пустое назначение → «' + esc(DEFAULT_DESCRIPTION) + '». Название сделки не подставляем.</div>' +
      '</div>' +
      '</details>' +
      '<div class="mnd-msg mnd-msg--error" data-role="create-error" hidden></div>' +
      '<button type="button" class="mnd-btn" data-role="create" disabled>Создать ссылку</button>' +
      '</div>'
    )
  }

  function copyRow(label, value, mod) {
    return (
      '<div class="mnd-row' + (mod ? ' mnd-row--' + mod : '') + '">' +
      '<span class="mnd-row__label">' + label + '</span>' +
      '<span class="mnd-row__value">' + money(value) + '</span>' +
      '<button type="button" class="mnd-copy" data-copy="' + esc(money(value).replace(/[\u00a0\u202f]/g, ' ')) + '" title="Скопировать">' +
      '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>' +
      '</button></div>'
    )
  }

  // ---------- калькулятор

  function openCalculator(ctx) {
    var root = node(modalHtml())
    var body = root.querySelector('[data-role="body"]')
    document.body.appendChild(root)
    document.documentElement.classList.add('mnd-lock')

    var s = {
      tariffs: [],
      limits: null,
      methodsAvailable: [],
      methodsSelected: [],
      tariffKey: null,
      amountType: 'cart_amount',
      amount: null,
      rows: null,
      term: null,
      onlySelected: false,
      calcSeq: 0,
      calculating: false,
      creating: false,
      timer: null,
    }
    var ui = {}

    function creditOn() {
      return s.methodsSelected.indexOf('credit') >= 0
    }

    function close() {
      if (s.creating) return
      clearTimeout(s.timer)
      document.removeEventListener('keydown', onKey)
      document.documentElement.classList.remove('mnd-lock')
      root.remove()
    }

    function onKey(e) {
      if (e.key === 'Escape') close()
    }

    root.querySelector('.mnd-close').addEventListener('click', close)
    root.addEventListener('mousedown', function (e) {
      if (e.target === root) close()
    })
    document.addEventListener('keydown', onKey)

    if (ctx.configError) {
      body.innerHTML =
        '<div class="mnd-state"><div class="mnd-state__title">Виджет не настроен</div>' +
        '<div class="mnd-hint">' + esc(ctx.configError.replace(/^Виджет не настроен: /, '')) +
        '. Администратор amoCRM может исправить это в настройках интеграции «Оплата Mandarin».</div></div>'
      return { close: close }
    }
    if (!ctx.leadId) {
      body.innerHTML = '<div class="mnd-state"><div class="mnd-state__title">Сначала сохраните сделку</div><div class="mnd-hint">Ссылка привязывается к сделке, поэтому у неё должен быть номер.</div></div>'
      return { close: close }
    }

    loadTariffs()
    return { close: close }

    function loadTariffs() {
      body.innerHTML =
        '<div class="mnd-state"><div class="mnd-spinner"></div><div class="mnd-hint">Загружаем настройки оплаты…</div></div>'
      Promise.all([ctx.call({ action: 'tariffs' }), ctx.amo.getClient(ctx.leadId).catch(function () { return null })])
        .then(function (res) {
          s.tariffs = res[0].tariffs || []
          s.limits = res[0].limits
          s.methodsAvailable = res[0].methods || ['credit']
          s.methodsSelected = (res[0].methodsDefault || s.methodsAvailable).slice()
          s.tariffKey = s.tariffs.length ? s.tariffs[0].key : null
          if (!s.tariffKey) throw new Error('В конфигурации нет тарифов / платёжных ссылок')
          renderForm(res[1] || { leadName: '', phone: '', email: '' })
        })
        .catch(function (err) {
          body.innerHTML =
            '<div class="mnd-state">' +
            '<div class="mnd-state__title">Не удалось получить настройки Mandarin</div>' +
            '<div class="mnd-hint">' + esc(errorText(err)) + '</div>' +
            '<button type="button" class="mnd-btn mnd-btn--ghost" data-role="retry">Повторить</button>' +
            '</div>'
          body.querySelector('[data-role="retry"]').addEventListener('click', loadTariffs)
        })
    }

    function tariff() {
      return s.tariffs.filter(function (t) { return t.key === s.tariffKey })[0]
    }

    function renderForm(client) {
      body.innerHTML = formHtml()
      ;[
        'methods', 'credit-block', 'credit-calc', 'tariffs', 'modes', 'amount-label', 'amount-hint', 'amount', 'limits',
        'terms', 'calc-error', 'details', 'offer', 'only-wrap', 'only', 'terms-hint', 'client-summary', 'phone',
        'email', 'description', 'create-error', 'create',
      ].forEach(function (role) {
        ui[role] = body.querySelector('[data-role="' + role + '"]')
      })

      ui.methods.innerHTML = s.methodsAvailable
        .map(function (m) {
          return (
            '<label class="mnd-check mnd-check--method">' +
            '<input type="checkbox" data-method="' + esc(m) + '"' + (s.methodsSelected.indexOf(m) >= 0 ? ' checked' : '') + '>' +
            '<span>' + esc(METHOD_LABELS[m] || m) + '</span></label>'
          )
        })
        .join('')
      if (s.methodsAvailable.length < 2) ui.methods.classList.add('mnd-methods--single')

      if (s.tariffs.length > 1) {
        ui.tariffs.innerHTML = s.tariffs
          .map(function (t) { return '<button type="button" data-tariff="' + esc(t.key) + '">' + esc(t.label) + '</button>' })
          .join('')
      } else if (ui.tariffs) {
        ui.tariffs.remove()
        ui.tariffs = null
      }
      ui.limits.textContent = 'от ' + money(s.limits.min) + ' до ' + money(s.limits.max)

      var phone = normalizePhone(client.phone)
      ui.phone.value = phone ? prettyPhone(phone) : ''
      ui.email.value = client.email || ''
      ui.description.value = ''
      updateClientSummary()

      body.addEventListener('click', onClick)
      ui.methods.addEventListener('change', onMethodsChange)
      ui.amount.addEventListener('input', onAmountInput)
      ui.amount.addEventListener('blur', function () {
        if (s.amount != null) ui.amount.value = s.amount.toLocaleString('ru-RU')
      })
      ui.only.addEventListener('change', function () {
        s.onlySelected = ui.only.checked
        renderOffer()
      })
      ui.phone.addEventListener('input', updateClientSummary)
      ui.email.addEventListener('input', updateClientSummary)

      applyCreditVisibility()
      renderControls()
      renderTerms()
      renderDetails()
      renderOffer()
      setTimeout(function () { ui.amount.focus() }, 50)
    }

    function onMethodsChange() {
      var next = []
      Array.prototype.forEach.call(ui.methods.querySelectorAll('[data-method]'), function (input) {
        if (input.checked) next.push(input.getAttribute('data-method'))
      })
      if (!next.length) {
        showError(ui['create-error'], 'Выберите хотя бы один способ оплаты')
        Array.prototype.forEach.call(ui.methods.querySelectorAll('[data-method]'), function (input) {
          input.checked = s.methodsSelected.indexOf(input.getAttribute('data-method')) >= 0
        })
        return
      }
      showError(ui['create-error'], null)
      var wasCredit = creditOn()
      s.methodsSelected = METHOD_ORDER.filter(function (m) { return next.indexOf(m) >= 0 })
      applyCreditVisibility()
      if (creditOn() && !wasCredit) recalc(true)
      else if (!creditOn()) {
        s.rows = null
        s.calculating = false
        clearTimeout(s.timer)
        renderAll()
      } else {
        renderOffer()
      }
    }

    function applyCreditVisibility() {
      var on = creditOn()
      ui['credit-block'].hidden = !on
      ui['credit-calc'].hidden = !on
      if (!on) {
        ui['amount-label'].textContent = 'Сумма оплаты'
        ui['amount-hint'].textContent = 'сумма счёта для клиента'
      }
    }

    function updateClientSummary() {
      var parts = [ui.phone.value.trim(), ui.email.value.trim()].filter(Boolean)
      ui['client-summary'].textContent = parts.length ? parts.join(' · ') : 'клиент введёт на странице оплаты'
    }

    function onClick(e) {
      var btn = e.target.closest('button')
      if (!btn || !body.contains(btn)) return
      if (btn.dataset.tariff) {
        if (s.tariffKey === btn.dataset.tariff) return
        s.tariffKey = btn.dataset.tariff
        s.term = null
        recalc(true)
      } else if (btn.dataset.mode) {
        if (s.amountType === btn.dataset.mode) return
        s.amountType = btn.dataset.mode
        recalc(true)
      } else if (btn.dataset.term) {
        s.term = Number(btn.dataset.term)
        renderTerms()
        renderDetails()
        renderOffer()
      } else if (btn.dataset.copy) {
        flashCopy(btn, btn.dataset.copy)
      } else if (btn === ui.create) {
        createLink()
      }
    }

    function onAmountInput() {
      var raw = ui.amount.value
      s.amount = raw.trim() ? parseAmount(raw) : null
      recalc(false)
    }

    function recalc(immediate) {
      clearTimeout(s.timer)
      s.rows = null
      s.calcSeq++
      showError(ui['calc-error'], null)
      showError(ui['create-error'], null)
      renderControls()

      if (!creditOn()) {
        s.calculating = false
        renderAll()
        return
      }

      var amount = s.amount
      if (amount == null) {
        s.calculating = false
        if (ui.amount.value.trim()) showError(ui['calc-error'], 'Введите сумму числом, например 150 000')
        renderAll()
        return
      }
      if (amount < s.limits.min || amount > s.limits.max) {
        s.calculating = false
        showError(ui['calc-error'], 'Сумма должна быть от ' + money(s.limits.min) + ' до ' + money(s.limits.max))
        renderAll()
        return
      }

      s.calculating = true
      renderAll()
      var seq = s.calcSeq
      var request = { action: 'calculate', tariff: s.tariffKey, amountType: s.amountType, amount: amount }
      s.timer = setTimeout(function () {
        ctx
          .call(request)
          .then(function (res) {
            if (seq !== s.calcSeq) return
            s.rows = res.rows || []
            var terms = s.rows.map(function (r) { return r.term })
            if (s.term == null || terms.indexOf(s.term) < 0) s.term = terms.length ? terms[terms.length - 1] : null
          })
          .catch(function (err) {
            if (seq !== s.calcSeq) return
            showError(ui['calc-error'], errorText(err))
          })
          .then(function () {
            if (seq !== s.calcSeq) return
            s.calculating = false
            renderAll()
          })
      }, immediate ? 0 : DEBOUNCE_MS)
    }

    function renderAll() {
      if (creditOn()) {
        renderTerms()
        renderDetails()
      }
      renderOffer()
    }

    function renderControls() {
      Array.prototype.forEach.call(body.querySelectorAll('[data-tariff]'), function (b) {
        b.classList.toggle('is-active', b.dataset.tariff === s.tariffKey)
      })
      Array.prototype.forEach.call(body.querySelectorAll('[data-mode]'), function (b) {
        b.classList.toggle('is-active', b.dataset.mode === s.amountType)
      })
      if (creditOn()) {
        ui['amount-label'].textContent = MODES[s.amountType].label
        ui['amount-hint'].textContent = MODES[s.amountType].hint
      }
    }

    function selectedRow() {
      if (!s.rows) return null
      return s.rows.filter(function (r) { return r.term === s.term })[0] || null
    }

    function renderTerms() {
      var rows = s.rows || []
      var terms = rows.length ? rows.map(function (r) { return r.term }) : tariff().terms
      ui.terms.classList.toggle('is-loading', s.calculating)
      ui.terms.innerHTML = terms
        .map(function (term) {
          var row = rows.filter(function (r) { return r.term === term })[0]
          var active = row && term === s.term
          return (
            '<button type="button" class="mnd-term' + (active ? ' is-active' : '') + '"' +
            (row ? ' data-term="' + term + '"' : ' disabled') + '>' +
            '<b>' + term + '</b>' +
            '<small>' + (row ? money(Math.round(row.monthly)) + '/мес' : '—') + '</small>' +
            '</button>'
          )
        })
        .join('')
    }

    function renderDetails() {
      var row = selectedRow()
      if (!row) {
        ui.details.innerHTML = s.calculating
          ? '<div class="mnd-skeleton"></div><div class="mnd-skeleton"></div><div class="mnd-skeleton"></div>'
          : '<div class="mnd-empty">Введите сумму — покажем платёж по каждому сроку</div>'
        return
      }
      ui.details.innerHTML =
        copyRow('Ежемесячный платёж', row.monthly, 'main') +
        copyRow('Сумма заявки (счёт)', row.invoiceAmount, s.amountType === 'settlement_amount' ? 'accent' : '') +
        copyRow('Компания получит', row.settlementAmount, s.amountType === 'cart_amount' ? 'accent' : '') +
        copyRow('Переплата клиента', row.overpayment, row.overpayment > 0 ? 'warn' : 'ok') +
        copyRow('Клиент заплатит всего', row.clientTotal)
    }

    function currentTerms() {
      return invoiceTerms(s.amountType, s.onlySelected, s.term, s.rows.map(function (r) { return r.term }))
    }

    function renderOffer() {
      if (!creditOn()) {
        var amountOk = s.amount != null && s.amount >= s.limits.min && s.amount <= s.limits.max
        ui.create.disabled = !amountOk || !s.methodsSelected.length || s.creating
        ui.create.textContent = s.creating
          ? 'Создаём ссылку…'
          : amountOk
            ? 'Создать ссылку на ' + money(s.amount)
            : 'Создать ссылку'
        return
      }
      var row = selectedRow()
      ui['only-wrap'].hidden = s.amountType !== 'cart_amount'
      ui.offer.hidden = !row
      ui.create.disabled = !row || !s.methodsSelected.length || s.creating
      if (!row) {
        ui.create.textContent = 'Создать ссылку'
        return
      }
      var terms = currentTerms()
      ui['terms-hint'].textContent =
        terms.length > 1
          ? 'Клиент сможет выбрать срок: ' + terms.join(', ') + ' мес.'
          : 'Клиенту будет доступен только срок ' + terms[0] + ' мес.'
      ui.create.textContent = s.creating ? 'Создаём ссылку…' : 'Создать ссылку на ' + money(row.invoiceAmount)
    }

    function createLink() {
      if (s.creating) return
      if (!s.methodsSelected.length) {
        showError(ui['create-error'], 'Выберите хотя бы один способ оплаты')
        return
      }
      var phoneRaw = ui.phone.value.trim()
      var phone = phoneRaw ? normalizePhone(phoneRaw) : ''
      if (phone === null) {
        ui.phone.closest('details').open = true
        showError(ui['create-error'], 'Телефон клиента: нужен российский номер, например +7 999 123-45-67')
        return
      }

      var payload = {
        action: 'create_invoice',
        tariff: s.tariffKey,
        leadId: ctx.leadId,
        paymentMethodTypes: s.methodsSelected.slice(),
        description: ui.description.value,
        phone: phone,
        email: ui.email.value.trim(),
      }
      var row = null
      var terms = []
      var t = tariff()

      if (creditOn()) {
        row = selectedRow()
        if (!row) return
        terms = currentTerms()
        payload.invoiceAmount = row.invoiceAmount
        payload.terms = terms
      } else {
        if (s.amount == null || s.amount < s.limits.min || s.amount > s.limits.max) return
        payload.invoiceAmount = s.amount
        payload.terms = []
      }

      s.creating = true
      showError(ui['create-error'], null)
      renderOffer()

      ctx
        .call(payload)
        .then(function (res) {
          var methodLabels = s.methodsSelected.map(function (m) { return METHOD_LABELS[m] || m }).join(', ')
          var note = 'автоматическое сообщение:\nОплата Mandarin: ' + money(payload.invoiceAmount) + ' · ' + methodLabels
          if (creditOn() && row) {
            note +=
              ', тариф «' + t.label + '», ' +
              (terms.length > 1 ? 'сроки ' + terms.join(', ') : 'срок ' + terms[0]) + ' мес.\n' +
              'На ' + row.term + ' мес.: ~' + money(Math.round(row.monthly)) + '/мес, компания получит ' + money(row.settlementAmount)
          }
          note += '\n' + res.url
          return saveToLead(ctx.amo, ctx.leadId, ctx.linkFieldId, res.url, note).then(function (problems) {
            s.creating = false
            renderDone(res, payload.invoiceAmount, row, terms, problems, methodLabels)
          })
        })
        .catch(function (err) {
          s.creating = false
          showError(ui['create-error'], 'Ссылка не создана: ' + errorText(err))
          renderOffer()
        })
    }

    function renderDone(res, amount, row, terms, problems, methodLabels) {
      body.removeEventListener('click', onClick)
      var sumLine = money(amount) + ' · ' + esc(methodLabels || '')
      if (row && terms && terms.length) {
        sumLine +=
          ' · ' + (terms.length > 1 ? 'до ' + terms[terms.length - 1] : terms[0]) +
          ' мес. · ~' + money(Math.round(row.monthly)) + '/мес'
      }
      body.innerHTML =
        '<div class="mnd-done">' +
        '<div class="mnd-done__icon"><svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></div>' +
        '<div class="mnd-done__title">Ссылка готова</div>' +
        '<div class="mnd-done__sum">' + sumLine + '</div>' +
        '<div class="mnd-link"><input readonly value="' + esc(res.url) + '"><button type="button" class="mnd-btn mnd-btn--small" data-copy-link>Скопировать</button></div>' +
        (problems.length
          ? '<div class="mnd-msg mnd-msg--warn">Не удалось сохранить в сделку (' + esc(problems.join(', ')) + ') — скопируйте ссылку вручную</div>'
          : '<div class="mnd-msg mnd-msg--ok">Ссылка сохранена в сделку и в ленту</div>') +
        '<div class="mnd-actions">' +
        '<a class="mnd-btn mnd-btn--ghost" href="' + esc(res.url) + '" target="_blank" rel="noopener">Открыть страницу оплаты</a>' +
        '<button type="button" class="mnd-btn mnd-btn--ghost" data-role="again">Новая ссылка</button>' +
        '</div></div>'
      body.querySelector('[data-copy-link]').addEventListener('click', function (e) {
        flashCopy(e.currentTarget, res.url, 'Скопировано')
      })
      body.querySelector('[data-role="again"]').addEventListener('click', function () {
        s.rows = null
        s.amount = null
        loadTariffs()
      })
    }

    function flashCopy(btn, text, doneLabel) {
      copyText(text).then(function () {
        btn.classList.add('is-done')
        var prev = btn.innerHTML
        if (doneLabel) btn.textContent = doneLabel
        setTimeout(function () {
          btn.classList.remove('is-done')
          if (doneLabel) btn.innerHTML = prev
        }, 1200)
      })
    }

    function showError(target, text) {
      if (!target) return
      target.hidden = !text
      target.textContent = text || ''
    }
  }

  // ---------- виджет amoCRM

  var CustomWidget = function () {
    var self = this

    function settings() {
      return self.get_settings() || {}
    }

    function ensureStyles() {
      if (document.getElementById('mnd-style')) return
      var s = settings()
      var link = document.createElement('link')
      link.id = 'mnd-style'
      link.rel = 'stylesheet'
      link.href = s.path + '/style.css?v=' + s.version
      document.head.appendChild(link)
    }

    function currentLeadId() {
      var card = window.AMOCRM && window.AMOCRM.data && window.AMOCRM.data.current_card
      return card && Number(card.id) > 0 ? Number(card.id) : null
    }

    this.callbacks = {
      render: function () {
        if (self.system().area !== 'lcard') return true
        ensureStyles()
        self.render_template({ caption: { class_name: 'mnd-caption' }, body: '', render: panelHtml() })
        return true
      },
      init: function () {
        return true
      },
      bind_actions: function () {
        $(document)
          .off('click.mnd')
          .on('click.mnd', '.mnd-open', function (e) {
            e.preventDefault()
            var s = settings()
            var ctx = { amo: createAmoApi(), leadId: currentLeadId(), linkFieldId: Number(s.link_field_id) || null }
            try {
              ctx.call = createMandarinApi({ config: parseConfig(s.bearer, s.config) })
            } catch (err) {
              ctx.configError = err.message
            }
            openCalculator(ctx)
          })
        return true
      },
      settings: function () {
        return true
      },
      onSave: function () {
        return true
      },
      destroy: function () {
        $(document).off('click.mnd')
      },
    }
    return this
  }

  CustomWidget.mnd = {
    openCalculator: openCalculator,
    panelHtml: panelHtml,
    parseConfig: parseConfig,
    createMandarinApi: createMandarinApi,
    parseMerchantFees: parseMerchantFees,
    normalizeCalculations: normalizeCalculations,
    makeOrderId: makeOrderId,
    buildInvoiceBody: buildInvoiceBody,
    invoiceTerms: invoiceTerms,
    normalizePhone: normalizePhone,
  }

  return CustomWidget
})
