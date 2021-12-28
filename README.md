# Mega Regression


## Importando os Dados

Copie o script abaixo e cole no console da página [`Resultados a Importar`](http://www.loterias.caixa.gov.br/wps/portal/loterias/landing/megasena/!ut/p/a1/04_Sj9CPykssy0xPLMnMz0vMAfGjzOLNDH0MPAzcDbwMPI0sDBxNXAOMwrzCjA0sjIEKIoEKnN0dPUzMfQwMDEwsjAw8XZw8XMwtfQ0MPM2I02-AAzgaENIfrh-FqsQ9wNnUwNHfxcnSwBgIDUyhCvA5EawAjxsKckMjDDI9FQE-F4ca/dl5/d5/L2dBISEvZ0FBIS9nQSEh/pw/Z7_HGK818G0K8DBC0QPVN93KQ10G1/res/id=historicoHTML/c=cacheLevelPage/=/),  em seguida clique enter e autorize os downloads.

Os arquivos baixados devem ser introduzidos no diretório data na raiz da aplicação.

> extract-data.js

```js
var labels = {},  data = [], key = 0
var table = document.querySelectorAll("body>table>tbody")
var header = table[0]
var body = table[1]


header.childNodes[0].childNodes.forEach((node) => {
    if (node.nodeName === "TH") {
        let key = node.childNodes[1].childNodes[1].innerHTML
                .trim()
                .toLowerCase()
                .replaceAll(" ", "_")
                .replaceAll("ã", "a")
                .replaceAll("ç", "c")

        labels[key] = node.childNodes[1].childNodes[1].innerHTML.trim()
    }
})

let keys = Object.keys(labels)
body.childNodes.forEach((node) => {
    if (node.nodeName == "TR" && node.hasChildNodes()) {
        let item = {}
        node.childNodes.forEach((tdNode) => {
            if(!tdNode.hasChildNodes() && tdNode.nodeName == "TD") {
                item[keys[key]] = tdNode.innerHTML.trim()
                key++
            }

            if ((tdNode.hasChildNodes() && tdNode.nodeName == "TD") && tdNode.childNodes.length === 1) {
                item[keys[key]] = tdNode.childNodes[0].data.trim()
                key++
            }
        })

        if (Object.keys(item).length > 0) data.push(item)
        key = 0
    }
})

function download(content, fileName, contentType) {
    var link = document.createElement("a")
    link.href = URL.createObjectURL(new Blob([content], {type: contentType}))
    link.download = fileName
    link.click()
}

let jlabels = JSON.stringify(labels)
let jdata = JSON.stringify(data)

download(jlabels, 'labels.json', 'text/plain')
download(jdata, 'data.json', 'text/plain')
download(JSON.stringify({labels: labels, data:data}), 'all-data.json', 'text/plain')
```


Att, [`Messias Dias`](https://github.com/messiasdias).