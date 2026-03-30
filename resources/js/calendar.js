import { Calendar } from '@fullcalendar/core'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'

window.hrCalendar = null;
document.addEventListener('DOMContentLoaded', function () {

    console.log("Calendar loaded")
    let calendarEl = document.getElementById('calendar')
  
    if (!calendarEl) return

    window.hrCalendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        firstDay: 1,
        locale: 'sr',
        weekends: true,
        events: {
            url: '/api/work-entries',
            extraParams: function () {
                return {
                    employee_id: document.getElementById('employeeFilter')?.value
                }
            }
        }
    })

    window.hrCalendar.render()

    // FILTER
    document.getElementById('employeeFilter')?.addEventListener('change', function () {
        calendar.refetchEvents()
    })
})