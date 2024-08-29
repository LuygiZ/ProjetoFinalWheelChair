<script setup>
import axios from 'axios'
import { useToast } from "vue-toastification"
import { useRouter, onBeforeRouteLeave } from 'vue-router'
import { ref, watch, computed, onMounted } from 'vue'

import TaskDetail from "./TaskDetail.vue"

const toast = useToast()
const router = useRouter()

const newTask = () => {
  return {
    id: null,
    owner_id: 1,  // Change it later
    project_id: null,
    completed: false,
    description: '',
    notes: '',
    total_hours: null
  }
}
const task = ref(newTask())
const projects = ref([])
const errors = ref(null)
const confirmationLeaveDialog = ref(null)
// String with the JSON representation after loading the project (new or edit)
let originalValueStr = ''
  
const loadTask = async (id) => {
  originalValueStr = ''
  errors.value = null
  if (!id || (id < 0)) {
    task.value = newTask()
    originalValueStr = JSON.stringify(task.value)
  } else {
      try {
        const response = await axios.get('tasks/' + id)
        task.value = response.data.data
        originalValueStr = JSON.stringify(task.value)
      } catch (error) {
        console.log(error)
      }
  }
}

const save = async () => {
  errors.value = null
  if (operation.value == 'insert') {
    try {
      const response = await axios.post('tasks', task.value)
      task.value = response.data.data
      originalValueStr = JSON.stringify(task.value)
      toast.success('Task #' + task.value.id + ' was created successfully.')
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Task was not created due to validation errors!')
      } else {
        toast.error('Task was not created due to unknown server error!')
      }
    }
  } else {
    try {
      const response = await axios.put('tasks/' + props.id, task.value)
      task.value = response.data.data
      originalValueStr = JSON.stringify(task.value)
      toast.success('Task #' + task.value.id + ' was updated successfully.')
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Task #' + props.id + ' was not updated due to validation errors!')
      } else {
        toast.error('Task #' + props.id + ' was not updated due to unknown server error!')
      }
    }
  }
}

const cancel = () => {
  originalValueStr = JSON.stringify(task.value)
  router.back()
}

const props = defineProps({
  id: {
    type: Number,
    default: null
  },
  fixedProject: {
    type: Number,
    default: null
  }
})


const operation = computed( () => (!props.id || props.id < 0) ? 'insert' : 'update')

  // beforeRouteUpdate was not fired correctly
  // Used this watcher instead to update the ID
watch(
  () => props.id,
  (newValue) => {
      loadTask(newValue)
    }, 
  { immediate: true}
)

let nextCallBack = null
const leaveConfirmed = () => {
  if (nextCallBack) {
    nextCallBack()
  }
}

onBeforeRouteLeave((to, from, next) => {
  nextCallBack = null
  let newValueStr = JSON.stringify(task.value)
  if (originalValueStr != newValueStr) {
    // Some value has changed - only leave after confirmation
    nextCallBack = next
    confirmationLeaveDialog.value.show()
  } else {
    // No value has changed, so we can leave the component without confirming
    next()
  }
})

onMounted (async () => {
  projects.value = []
  try {
    const response = await axios.get('projects')
    projects.value = response.data.data
  } catch (error) {
    console.log(error)
  }
})
</script>


<template>
  <confirmation-dialog
    ref="confirmationLeaveDialog"
    confirmationBtn="Discard changes and leave"
    msg="Do you really want to leave? You have unsaved changes!"
    @confirmed="leaveConfirmed"
  >
  </confirmation-dialog>  

  <task-detail
    :operationType="operation"
    :task="task"
    :projects="projects"
    :fixedProject="fixedProject"
    :errors="errors"
    @save="save"
    @cancel="cancel"
  ></task-detail>
</template>