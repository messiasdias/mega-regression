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