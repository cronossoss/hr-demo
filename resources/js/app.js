import './bootstrap';
import './calendar'

window.openEmployee = function(id) {
    window.location.href = `/employees/${id}`;
};
