<script setup>
import axios from 'axios'
import { useToast } from "vue-toastification"
import { ref, watch, computed } from "vue"

const toast = useToast()

const props = defineProps({
  tasks: {
    type: Array,
    default: () => [],
  },
  showId: {
    type: Boolean,
    default: true,
  },
  showCompleted: {
    type: Boolean,
    default: true,
  },
  showOwner: {
    type: Boolean,
    default: true,
  },
  showProject: {
    type: Boolean,
    default: true,
  },
  showCompletedButton: {
    type: Boolean,
    default: true,
  },
  showEditButton: {
    type: Boolean,
    default: true,
  },
  showDeleteButton: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(["completeToggled", "edit", "deleted"])

const editingTasks = ref(props.tasks)
const taskToDelete = ref(null)
const deleteConfirmationDialog = ref(null)

watch(
  () => props.tasks,
  (newTasks) => {
    editingTasks.value = newTasks
  }
)

// Alternative to previous watch
// watchEffect(() => {
//   editingTasks.value = props.tasks
// })

const toogleClick = async (task) => {
    try {
    const response = await axios.patch('tasks/' + task.id + '/completed', { completed: !task.completed })
    task.completed = response.data.data.completed
    emit("completeToggled", task)
  } catch (error) {
    console.log(error)
  }
}
const editClick = (task) => {
  emit("edit", task)
}

const deleteClick = (task) => {
  taskToDelete.value = task
  deleteConfirmationDialog.value.show()
}

const deleteTaskConfirmed = async () => {
  try {
    const response = await axios.delete('tasks/' + taskToDelete.value.id)
    let deletedTask = response.data.data
    toast.info(`Task ${taskToDeleteDescription.value} was deleted`)
    emit("deleted", deletedTask)
  } catch (error) {
    console.log(error)
    toast.error(`It was not possible to delete Task ${taskToDeleteDescription.value}!`)
  }
}

const taskToDeleteDescription = computed(() => taskToDelete.value
  ? `#${taskToDelete.value.id} (${taskToDelete.value.description})`
  : "")

</script>

<template>
  <confirmation-dialog
    ref="deleteConfirmationDialog"
    confirmationBtn="Delete task"
    :msg="`Do you really want to delete the task ${taskToDeleteDescription}?`"
    @confirmed="deleteTaskConfirmed"
  >
  </confirmation-dialog>

  <table class="table">
    <thead>
      <tr>
        <th v-if="showId">#</th>
        <th class="text-center" v-if="showCompleted">Completed</th>
        <th>Description</th>
        <th v-if="showOwner">Owner</th>
        <th v-if="showProject">Project</th>
        <th v-if="showCompletedButton || showEditButton || showDeleteButton"></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="task in editingTasks" :key="task.id">
        <td v-if="showId">{{ task.id }}</td>
        <td class="text-center" v-if="showCompleted">
          {{ task.completed ? "yes" : "-" }}
        </td>
        <td>
          <span :class="{ completed: task.completed }">{{ task.description }}</span>
        </td>
        <td v-if="showOwner">{{ task.owner_name }}</td>
        <td v-if="showProject">{{ task.project_name }}</td>
        <td
          class="text-end"
          v-if="showCompletedButton || showEditButton || showDeleteButton"
        >
          <div class="d-flex justify-content-end">
            <button
              class="btn btn-xs btn-light"
              @click="toogleClick(task)"
              v-if="showCompletedButton"
            >
              <i
                class="bi bi-xs"
                :class="{
                  'bi-square': !task.completed,
                  'bi-check2-square': task.completed,
                }"
              ></i>
            </button>

            <button
              class="btn btn-xs btn-light"
              @click="editClick(task)"
              v-if="showEditButton"
            >
              <i class="bi bi-xs bi-pencil"></i>
            </button>

            <button
              class="btn btn-xs btn-light"
              @click="deleteClick(task)"
              v-if="showDeleteButton"
            >
              <i class="bi bi-xs bi-x-square-fill"></i>
            </button>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<style scoped>
.completed {
  text-decoration: line-through;
}

button {
  margin-left: 3px;
  margin-right: 3px;
}
</style>
