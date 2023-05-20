let recordTypes, types, token, recordPanels

document.addEventListener("DOMContentLoaded", () => {
  recordTypes = ["A", "AAAA", "CNAME", "MX", "NS", "PTR", "SRV", "SOA", "TXT", "CAA", "DS", "DNSKEY"]

  types = document.getElementById("type-group").children
  token = document.getElementsByName("_token")[0].value
  recordPanels = document.getElementsByClassName("accordion-body")

for (const type of types) {
  type.addEventListener("click", function() {
    this.classList.toggle("btn-success")
    if (this.innerHTML == "ALL") {
      for (const category of types) {
        if (this.classList.length == 3)
          category.classList.add("btn-success")
        else
          category.classList.remove("btn-success")
      }
    }
  })
}
});

function enterPressed(e) {
  e.preventDefault();
  lookup();
}

function lookup() {
  const domain = document.getElementById("dns").value
  const dns_server = document.getElementById("dns-server").value

  let categories = []
  for (const type of types) {
    if (type.classList.length == 3) {
      categories.push(type.innerHTML)
    }
  }

  const data = {
    domain,
    categories,
    dns_server
  }

  fetch(
    '/dns-lookup',
    {
      method: "POST",
      headers: {
        'Content-Type': 'application/json',
        "X-CSRF-Token": token
      },
      body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
      const { records, countryCode, whoisserver } = result
      const res = records

      for (const idx in recordTypes) {
        const records = res.filter(record => {
          return record.type == recordTypes[idx]
        })
        const visible = categories.indexOf(recordTypes[idx]) != -1
        console.log(recordTypes[idx], categories)

        if (!visible) recordPanels[idx].parentElement.parentElement.classList.add('d-none')
        else recordPanels[idx].parentElement.parentElement.classList.remove('d-none')

        if (!records.length) {
          recordPanels[idx].innerHTML = `<p>No Records Found</p>`
          continue
        }

        let innerHTML = `<table class="table">
          <thead>
            <tr>
              ${
                Object.keys(records[0]).map(k => `<th>${k}</th>`).join("")
              }
            </tr>
          </thead>
          <tbody>
        `
        for (const record of records) {
          innerHTML += `
            <tr>
              ${
                Object.keys(record).map(k => {
                  if (k == 'country') return `<td><img src="https://dnschecker.org/themes/common/images/flags/svg/${record[k].toLowerCase()}.svg" style="width: 30px;" /></td>`
                  return `<td>${record[k]}</td>`
                }).join("")
              }
            </tr>
          `
        }

        innerHTML += `
          </tbody>
        </table>
        `
        recordPanels[idx].innerHTML = innerHTML
      }
    })
}