/*
 * select_many input's supporting code
 */

var smObserver = function(event) {
  // delete itself and recreate option in select
  check = event.element();
  up_div = check.up('div');
  select = $(check.className);
  new_opt = new Element('option', {'value' : check.getAttribute('value')});
  new_opt.appendChild(document.createTextNode(up_div.down('span').firstChild.data));
  check.remove();
  up_div.remove();
  select.appendChild(new_opt);
}

selectManyCheckBehavior = Behavior.create({
  onchange : smObserver
});

selectManyBehavior = Behavior.create({
  onchange: function(event) {
    option = $(this.element.options[this.element.selectedIndex]);
    opt_value = option.value;
    // create new div with checkbox to delete it self
    new_div = new Element('div', {'id' : this.element.identify()+'_'+opt_value});
    new_check = new Element('input', {
      'type'  : 'checkbox',
      'value' : opt_value,
      'class' : this.element.identify(),
      'name'  : this.element.getAttribute('name')+'[]'
    });
    new_check.checked = true;
    // apply observer
    new_check.observe('change', smObserver);
    new_div.appendChild(new_check);
    text_span = new Element('span').update(option.text);
    new_div.appendChild(text_span);
    this.element.insert({after: new_div});
    this.element.options[0].selected = true;
    // remove option from select
    option.remove();
  }
});

Event.addBehavior({
  'select.select_many' : selectManyBehavior,
  '.select_many_check input[type="checkbox"]' : selectManyCheckBehavior
});
